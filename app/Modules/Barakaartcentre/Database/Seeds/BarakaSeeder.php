<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BarakaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Admin User
        $this->db->table('baraka_users')->ignore(true)->insert([
            'name'          => 'Admin Robai',
            'email'         => 'admin@barakaartcentre.org',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role'          => 'admin',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        // 2. Seed Services Structure (From PDF)
        $services = [
            // Revenue
            ['title' => 'Fine Art Commissions', 'type' => 'Revenue', 'short_description' => 'Original paintings, prints, murals.'],
            ['title' => 'Digital Design Services', 'type' => 'Revenue', 'short_description' => 'Logos, posters, social media graphics.'],
            ['title' => 'Merchandise & Prints', 'type' => 'Revenue', 'short_description' => 'Tote bags, mugs, limited edition prints.'],
            // Community
            ['title' => 'Art Workshops', 'type' => 'Community', 'short_description' => 'Youth and adult workshops focusing on painting and emotional expression.'],
            ['title' => 'Design for Good', 'type' => 'Community', 'short_description' => 'Free or subsidized creative projects for local NGOs.'],
            ['title' => 'Mentorship', 'type' => 'Community', 'short_description' => 'Guidance and skill-sharing for young coastal creatives.'],
        ];

        foreach ($services as $service) {
            $service['icon_or_image'] = 'https://picsum.photos/seed/'.md5($service['title']).'/100/100';
            $service['created_at'] = date('Y-m-d H:i:s');
            $service['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('baraka_services')->insert($service);
        }

        // 3. Seed Sample Artworks
        $artworks = [
            ['title' => 'Old Town Mural', 'category' => 'Mural', 'description' => 'A vibrant collaborative piece.'],
            ['title' => 'Abstract Expression', 'category' => 'Original', 'description' => 'An original fine art commission.'],
            ['title' => 'Digital Branding Demo', 'category' => 'Student Project', 'description' => 'Design for good initiative example.'],
            ['title' => 'Swahili Coastal Vibe', 'category' => 'Original', 'description' => 'Oil on canvas inspired by Mombasa.'],
            ['title' => 'Youth Art Workshop Display', 'category' => 'Print', 'description' => 'Limited edition print from workshop results.'],
        ];

        foreach ($artworks as $i => $art) {
            $art['image_path'] = 'https://picsum.photos/seed/art_'.$i.'/600/'.rand(400, 800);
            $art['price'] = ($art['category'] === 'Original') ? rand(5000, 20000) : null;
            $art['is_sold'] = false;
            $art['created_at'] = date('Y-m-d H:i:s');
            $art['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('baraka_artworks')->insert($art);
        }

        // 4. Seed Upcoming Workshops
        $workshops = [
            ['title' => 'Draw Your Mood', 'event_date' => date('Y-m-d', strtotime('+7 days')), 'time' => '10:00 AM - 1:00 PM', 'fee' => 1500, 'description' => 'Express feelings through colors and shapes.'],
            ['title' => 'Mindful Observation', 'event_date' => date('Y-m-d', strtotime('+14 days')), 'time' => '2:00 PM - 5:00 PM', 'fee' => 2000, 'description' => 'Paint an object slowly, observing details.'],
            ['title' => 'Creative Play Abstract', 'event_date' => date('Y-m-d', strtotime('+30 days')), 'time' => '10:00 AM - 4:00 PM', 'fee' => 3500, 'description' => 'Free abstract painting for dopamine release.'],
        ];

        foreach ($workshops as $i => $ws) {
            $ws['image_path'] = 'https://picsum.photos/seed/workshop_'.$i.'/600/400';
            $ws['created_at'] = date('Y-m-d H:i:s');
            $ws['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('baraka_workshops')->insert($ws);
        }
    }
}
