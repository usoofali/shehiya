#!/usr/bin/env python3
import urllib.request
import urllib.parse
import json
import argparse
import sys
import re
import datetime
import time
from concurrent.futures import ThreadPoolExecutor, as_completed

BASE_URL = "https://cvr.inecnigeria.org"
API_BASE = f"{BASE_URL}/PublicApi"

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json, text/javascript, */*; q=0.01',
    'X-Requested-With': 'XMLHttpRequest'
}

def fetch_json(url, max_retries=3, delay=2):
    for attempt in range(max_retries):
        req = urllib.request.Request(url, headers=HEADERS)
        try:
            with urllib.request.urlopen(req, timeout=20) as response:
                content = response.read().decode('utf-8', errors='ignore')
                return json.loads(content)
        except Exception as e:
            if attempt == max_retries - 1:
                sys.stderr.write(f"Error fetching {url} after {max_retries} attempts: {e}\n")
            else:
                time.sleep(delay * (attempt + 1))
    return []

def get_states():
    # Scrape state select options from main page
    req = urllib.request.Request(f"{BASE_URL}/pu", headers=HEADERS)
    try:
        with urllib.request.urlopen(req, timeout=15) as response:
            html = response.read().decode('utf-8', errors='ignore')
            select_match = re.search(r'<select[^>]*id="SearchStateId"[^>]*>(.*?)</select>', html, re.DOTALL)
            if not select_match:
                return []
            options = re.findall(r'<option value="(\d+)">(.*?)</option>', select_match.group(1))
            states = []
            for val, text in options:
                text = text.strip()
                if not val or val == "0" or "SELECT" in text:
                    continue
                # Extract code if present (e.g., '01 - ABIA')
                parts = text.split(' - ', 1)
                code = parts[0] if len(parts) == 2 else None
                name = parts[1] if len(parts) == 2 else text
                states.append({'id': int(val), 'code': code, 'name': name})
            return states
    except Exception as e:
        sys.stderr.write(f"Error fetching main page: {e}\n")
        return []

def get_lgas(state_id):
    url = f"{API_BASE}/lgas/1/Search?data%5BSearch%5D%5Bstate_id%5D={state_id}"
    data = fetch_json(url)
    lgas = []
    if isinstance(data, list) and len(data) > 0 and isinstance(data[0], dict):
        for k, v in data[0].items():
            if k in ("0", "selected") or not v or "SELECT" in str(v):
                continue
            parts = str(v).split(' - ', 1)
            name = parts[1] if len(parts) == 2 else str(v)
            lgas.append({'id': int(k), 'state_id': state_id, 'name': name.strip()})
    return lgas

def get_wards(lga_id):
    url = f"{API_BASE}/wards/1/Search?data%5BSearch%5D%5Blocal_government_id%5D={lga_id}"
    data = fetch_json(url)
    wards = []
    if isinstance(data, list) and len(data) > 0 and isinstance(data[0], dict):
        for k, v in data[0].items():
            if k in ("0", "selected") or not v or "SELECT" in str(v):
                continue
            parts = str(v).split(' - ', 1)
            name = parts[1] if len(parts) == 2 else str(v)
            wards.append({'id': int(k), 'lga_id': lga_id, 'name': name.strip()})
    return wards

def get_polling_units(ward_id):
    url = f"{API_BASE}/pus/1/Search?data%5BSearch%5D%5Bregistration_area_id%5D={ward_id}"
    data = fetch_json(url)
    pus = []
    if isinstance(data, list) and len(data) > 0 and isinstance(data[0], dict):
        for k, v in data[0].items():
            if k in ("0", "selected") or not v or "SELECT" in str(v):
                continue
            text = str(v).strip()
            parts = text.split(' - ', 1)
            code = parts[0] if len(parts) == 2 else None
            name = parts[1] if len(parts) == 2 else text
            pus.append({'id': int(k), 'ward_id': ward_id, 'code': code, 'name': name.strip()})
    return pus

def sql_quote(val):
    if val is None:
        return "NULL"
    if isinstance(val, (int, float)):
        return str(val)
    s = str(val).replace('\\', '\\\\').replace("'", "''")
    return f"'{s}'"

def format_insert_batch(table, columns, rows_list, dialect="mysql", batch_size=500):
    if not rows_list:
        return []
    
    if dialect in ("mysql", "mariadb"):
        insert_prefix = f"INSERT IGNORE INTO `{table}`"
    elif dialect == "sqlite":
        insert_prefix = f"INSERT OR IGNORE INTO `{table}`"
    elif dialect in ("postgres", "postgresql"):
        insert_prefix = f"INSERT INTO {table}"
    else:
        insert_prefix = f"INSERT INTO `{table}`"
        
    cols_str = ", ".join([f"`{c}`" if dialect not in ("postgres", "postgresql") else c for c in columns])
    
    sql_statements = []
    for i in range(0, len(rows_list), batch_size):
        batch = rows_list[i:i + batch_size]
        values_strs = []
        for row in batch:
            val_str = ", ".join(sql_quote(val) for val in row)
            values_strs.append(f"  ({val_str})")
        
        stmt = f"{insert_prefix} ({cols_str}) VALUES\n" + ",\n".join(values_strs)
        if dialect in ("postgres", "postgresql"):
            stmt += "\nON CONFLICT DO NOTHING;"
        else:
            stmt += ";"
        sql_statements.append(stmt)
        
    return sql_statements

def generate_sql(data, dialect="mysql", batch_size=500):
    now_str = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    
    if not data:
        return "-- No data scraped to generate SQL.\n"
        
    sql_output = []
    sql_output.append(f"-- SQL Dump generated by scrape_inec_pu.py")
    sql_output.append(f"-- Dialect: {dialect.upper()}")
    sql_output.append(f"-- Timestamp: {now_str}")
    sql_output.append("")
    
    # Case 1: Polling units list (from --ward)
    if isinstance(data, list) and len(data) > 0 and 'ward_id' in data[0]:
        pu_rows = []
        for pu in data:
            pu_rows.append((pu.get('id'), pu.get('ward_id'), pu.get('name'), pu.get('code'), now_str, now_str))
        stmts = format_insert_batch('polling_units', ['id', 'ward_id', 'name', 'code', 'created_at', 'updated_at'], pu_rows, dialect, batch_size)
        sql_output.extend(stmts)
        return "\n\n".join(sql_output) + "\n"
        
    # Case 2: Hierarchical LGA tree (from --lga)
    if isinstance(data, list) and len(data) > 0 and 'wards' in data[0] and 'lgas' not in data[0]:
        ward_rows = []
        pu_rows = []
        ward_id_counter = 1
        pu_id_counter = 1
        for lga in data:
            lga_id = lga.get('id')
            for ward in lga.get('wards', []):
                ward_id = ward_id_counter
                ward_id_counter += 1
                ward_rows.append((ward_id, lga_id, ward.get('name'), now_str, now_str))
                for pu in ward.get('polling_units', []):
                    pu_id = pu_id_counter
                    pu_id_counter += 1
                    pu_rows.append((pu_id, ward_id, pu.get('name'), pu.get('code'), now_str, now_str))
        if ward_rows:
            sql_output.append("-- Table: wards")
            sql_output.extend(format_insert_batch('wards', ['id', 'lga_id', 'name', 'created_at', 'updated_at'], ward_rows, dialect, batch_size))
            sql_output.append("")
        if pu_rows:
            sql_output.append("-- Table: polling_units")
            sql_output.extend(format_insert_batch('polling_units', ['id', 'ward_id', 'name', 'code', 'created_at', 'updated_at'], pu_rows, dialect, batch_size))
            sql_output.append("")
        return "\n\n".join(sql_output).strip() + "\n"

    # Case 3: States list without LGAs (from --states-only)
    if isinstance(data, list) and len(data) > 0 and 'code' in data[0] and 'lgas' not in data[0]:
        state_rows = []
        for state in data:
            state_rows.append((state.get('id'), state.get('name'), state.get('code'), now_str, now_str))
        stmts = format_insert_batch('states', ['id', 'name', 'code', 'created_at', 'updated_at'], state_rows, dialect, batch_size)
        sql_output.extend(stmts)
        return "\n\n".join(sql_output) + "\n"
        
    # Case 4: Hierarchical State tree (from full scrape or --state)
    state_rows = []
    lga_rows = []
    ward_rows = []
    pu_rows = []
    
    lga_id_counter = 1
    ward_id_counter = 1
    pu_id_counter = 1
    
    for state in data:
        state_id = state.get('id')
        state_rows.append((state_id, state.get('name'), state.get('code'), now_str, now_str))
        
        for lga in state.get('lgas', []):
            lga_id = lga_id_counter
            lga_id_counter += 1
            lga_rows.append((lga_id, state_id, lga.get('name'), now_str, now_str))
            
            for ward in lga.get('wards', []):
                ward_id = ward_id_counter
                ward_id_counter += 1
                ward_rows.append((ward_id, lga_id, ward.get('name'), now_str, now_str))
                
                for pu in ward.get('polling_units', []):
                    pu_id = pu_id_counter
                    pu_id_counter += 1
                    pu_rows.append((pu_id, ward_id, pu.get('name'), pu.get('code'), now_str, now_str))
                    
    if state_rows:
        sql_output.append("-- Table: states")
        sql_output.extend(format_insert_batch('states', ['id', 'name', 'code', 'created_at', 'updated_at'], state_rows, dialect, batch_size))
        sql_output.append("")
    if lga_rows:
        sql_output.append("-- Table: lgas")
        sql_output.extend(format_insert_batch('lgas', ['id', 'state_id', 'name', 'created_at', 'updated_at'], lga_rows, dialect, batch_size))
        sql_output.append("")
    if ward_rows:
        sql_output.append("-- Table: wards")
        sql_output.extend(format_insert_batch('wards', ['id', 'lga_id', 'name', 'created_at', 'updated_at'], ward_rows, dialect, batch_size))
        sql_output.append("")
    if pu_rows:
        sql_output.append("-- Table: polling_units")
        sql_output.extend(format_insert_batch('polling_units', ['id', 'ward_id', 'name', 'code', 'created_at', 'updated_at'], pu_rows, dialect, batch_size))
        sql_output.append("")
        
    return "\n\n".join(sql_output).strip() + "\n"

def scrape_lga_tree(lga_id):
    # sys.stderr.write(f"Fetching wards for LGA ID: {lga_id}...\n")
    wards = get_wards(lga_id)
    lga_data = {
        'id': lga_id,
        'wards': []
    }
    # sys.stderr.write(f"  Found {len(wards)} wards in LGA ID {lga_id}. Fetching polling units...\n")
    with ThreadPoolExecutor(max_workers=4) as executor:
        future_to_ward = {executor.submit(get_polling_units, ward['id']): ward for ward in wards}
        ward_pus_map = {}
        for future in as_completed(future_to_ward):
            ward = future_to_ward[future]
            try:
                pus = future.result()
                ward_pus_map[ward['id']] = pus
                # sys.stderr.write(f"    [+] Ward {ward['name']}: {len(pus)} PUs\n")
            except Exception as exc:
                sys.stderr.write(f"    [!] Error fetching PUs for ward {ward['id']}: {exc}\n")
                ward_pus_map[ward['id']] = []
                
    for ward in wards:
        lga_data['wards'].append({
            'id': ward['id'],
            'name': ward['name'],
            'polling_units': ward_pus_map.get(ward['id'], [])
        })
    return [lga_data]

def scrape_state_tree(state):
    state_id = state['id']
    sys.stderr.write(f"Fetching LGAs for State: {state['name']} (ID: {state_id})...\n")
    lgas = get_lgas(state_id)
    state_data = {
        'id': state_id,
        'code': state['code'],
        'name': state['name'],
        'lgas': []
    }
    
    total_lgas = len(lgas)
    for idx, lga in enumerate(lgas, 1):
        sys.stderr.write(f"  [{state['name']} - {idx}/{total_lgas}] Scraping LGA: {lga['name']}...\n")
        wards = get_wards(lga['id'])
        lga_data = {
            'id': lga['id'],
            'name': lga['name'],
            'wards': []
        }
        with ThreadPoolExecutor(max_workers=4) as executor:
            future_to_ward = {executor.submit(get_polling_units, ward['id']): ward for ward in wards}
            ward_pus_map = {}
            for future in as_completed(future_to_ward):
                ward = future_to_ward[future]
                try:
                    pus = future.result()
                    ward_pus_map[ward['id']] = pus
                    # sys.stderr.write(f"    [+] Ward {ward['name']}: {len(pus)} PUs\n")
                except Exception as exc:
                    sys.stderr.write(f"    [!] Error fetching PUs for ward {ward['id']}: {exc}\n")
                    ward_pus_map[ward['id']] = []
                    
        for ward in wards:
            lga_data['wards'].append({
                'id': ward['id'],
                'name': ward['name'],
                'polling_units': ward_pus_map.get(ward['id'], [])
            })
        state_data['lgas'].append(lga_data)
    return state_data

def main():
    parser = argparse.ArgumentParser(description="INEC CVR Polling Unit Scraper & SQL Generator")
    parser.add_argument("--states-only", action="store_true", help="List all states")
    parser.add_argument("--state", type=int, help="Scrape specific state ID")
    parser.add_argument("--lga", type=int, help="Scrape specific LGA ID for Wards")
    parser.add_argument("--ward", type=int, help="Scrape specific Ward ID for Polling Units")
    parser.add_argument("--sql", action="store_true", help="Generate SQL insert statements instead of JSON")
    parser.add_argument("--format", choices=["json", "sql"], default="json", help="Output format (json or sql)")
    parser.add_argument("--dialect", choices=["mysql", "sqlite", "postgres", "standard"], default="mysql", help="SQL dialect when generating SQL")
    parser.add_argument("--output", "-o", type=str, help="Save output to a file instead of printing to stdout")
    parser.add_argument("--stdout", action="store_true", help="Print output to terminal stdout instead of saving to a file")
    args = parser.parse_args()

    output_format = "sql" if args.sql else args.format

    if args.states_only:
        data = get_states()
    elif args.ward:
        data = get_polling_units(args.ward)
    elif args.lga:
        data = scrape_lga_tree(args.lga)
    else:
        states = get_states()
        if args.state:
            target_states = [s for s in states if s['id'] == args.state]
        else:
            target_states = states

        data = []
        for s in target_states:
            sys.stderr.write(f"Scraping state: {s['name']} (ID: {s['id']})...\n")
            tree = scrape_state_tree(s)
            data.append(tree)

    if output_format == "sql":
        output_str = generate_sql(data, dialect=args.dialect)
    else:
        output_str = json.dumps(data, indent=2)

    if args.stdout or args.output == "-":
        print(output_str)
    else:
        if not args.output:
            ext = "sql" if output_format == "sql" else "json"
            if args.states_only:
                args.output = f"inec_states.{ext}"
            elif args.ward:
                args.output = f"inec_ward_{args.ward}.{ext}"
            elif args.lga:
                args.output = f"inec_lga_{args.lga}.{ext}"
            elif args.state:
                args.output = f"inec_state_{args.state}.{ext}"
            else:
                args.output = f"inec_all_polling_units.{ext}"

        with open(args.output, "w", encoding="utf-8") as f:
            f.write(output_str)
        sys.stderr.write(f"\n[+] Successfully saved {output_format.upper()} output to file: {args.output}\n")

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        sys.stderr.write("\n[!] Scraping cancelled by user (Ctrl+C).\n")
        import os
        os._exit(1)
