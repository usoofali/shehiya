<?php

namespace Database\Seeders;

use App\Models\MovementContent;
use Illuminate\Database\Seeder;

class MovementContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            [
                'key' => 'hero_title',
                'title' => 'Empowering the People through Trust & Unity',
                'body' => 'Welcome to the Shaihiyya Amanar Jagora Management Platform (SA) — dedicated to grassroots mobilization, structured organizational coordination, and sustainable community leadership across the nation.',
            ],
            [
                'key' => 'about_section',
                'title' => 'About Shaihiyya Amanar Jagora',
                'body' => 'The Shaihiyya Amanar Jagora is founded on the core values of integrity (Amana), transparent grassroots mobilization, and inclusive democratic leadership. Our support organization structures span from the national leadership down to the polling unit wards, ensuring every voice is heard and verified.',
            ],
            [
                'key' => 'vision_section',
                'title' => 'Vision of the ORGANIZATION',
                'body' => "The vision of the SHAIHIYYA AMANAR JAGORA SUPPORT ORGANIZATION is founded on the following objectives:\n\nTo create widespread political awareness across Nigeria regarding Jagora's future political aspirations, particularly in view of any future national leadership ambitions he may pursue.\n\nTo protect and promote the good image and reputation of Jagora by providing factual information and positive public engagement in response to political misinformation and misrepresentation.\n\nTo remain loyal and accountable solely to Jagora's political support organization and to operate in accordance with its objectives and directives.\n\nTo serve as an effective communication platform for conveying information, messages, and mobilization efforts from Jagora's political support organization to communities across Zamfara State and Nigeria as a whole.\n\nTo operate under the guidance of respected and experienced leaders who will promote discipline, unity, and responsible conduct among supporters, ensuring that all activities reflect positively on Jagora's reputation and political vision.",
            ],
            [
                'key' => 'mission_section',
                'title' => 'Mission of the ORGANIZATION',
                'body' => "The mission of the SHAIHIYYA AMANAR JAGORA SUPPORT ORGANIZATION is to:\n\nDemonstrate unwavering solidarity and support for Jagora, Distinguished Senator Dr. Abdul'Aziz Abubakar Yari, in the pursuit of his political vision and aspirations.\n\nServe as responsible ambassadors of Jagora by upholding his dignity, protecting his reputation, and promoting the values he represents.\n\nShowcase Jagora's leadership, influence, and contributions within Nigeria's contemporary political landscape.\n\nPromote awareness of Jagora's developmental initiatives, leadership achievements, and service to his constituents among neighboring states and across the nation.\n\nEnlighten and educate the public on Jagora's contributions, goodwill, and commitment to the development of Zamfara State and Nigeria as a whole.",
            ],
        ];

        foreach ($contents as $content) {
            MovementContent::updateOrCreate(['key' => $content['key']], $content);
        }
    }
}
