<?php

namespace Database\Seeders;

use App\Models\BoardMember;
use App\Models\Board;
use App\Models\CouncilMember;
use App\Models\ContactEntry;
use App\Models\Event;
use App\Models\HeroImage;
use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@slia.local'],
            [
                'name' => 'admin',
                'password' => Hash::make('SliaAdmin@2026'),
                'role' => 'super_admin',
                'board_key' => null,
            ]
        );

        $boardAdmins = [
            ['name' => 'bom', 'email' => 'bom@slia.local', 'password' => 'BomAdmin@2026', 'board_key' => 'management'],
            ['name' => 'bae', 'email' => 'bae@slia.local', 'password' => 'BaeAdmin@2026', 'board_key' => 'bae'],
            ['name' => 'pab', 'email' => 'pab@slia.local', 'password' => 'PabAdmin@2026', 'board_key' => 'pab'],
            ['name' => 'bap', 'email' => 'bap@slia.local', 'password' => 'BapAdmin@2026', 'board_key' => 'bap'],
        ];

        foreach ($boardAdmins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'role' => 'board_admin',
                    'board_key' => $admin['board_key'],
                ]
            );
        }

        $councilMembers = [
            ['name' => 'Ar. [Name]', 'designation' => 'President', 'sort_order' => 1],
            ['name' => 'Ar. [Name]', 'designation' => 'Senior Vice President', 'sort_order' => 2],
            ['name' => 'Ar. [Name]', 'designation' => 'Junior Vice President', 'sort_order' => 3],
            ['name' => 'Ar. [Name]', 'designation' => 'Honorary Secretary', 'sort_order' => 4],
            ['name' => 'Ar. [Name]', 'designation' => 'Honorary Treasurer', 'sort_order' => 5],
            ['name' => 'Ar. [Name]', 'designation' => 'Editor', 'sort_order' => 12],
            ['name' => 'Ar. [Name]', 'designation' => 'Chairman - BAE', 'sort_order' => 12],
            ['name' => 'Ar. [Name]', 'designation' => 'Immediate Past President', 'sort_order' => 12],
        ];

        foreach ($councilMembers as $member) {
            CouncilMember::firstOrCreate(
                ['designation' => $member['designation']],
                $member + ['image' => '', 'is_active' => true]
            );
        }

        $boardKeys = ['trustees', 'management', 'bae', 'pab', 'bap'];
        foreach ($boardKeys as $boardKey) {
            $members = [
                ['designation' => 'Chairman', 'sort_order' => 1],
                ['designation' => 'Member', 'sort_order' => 2],
                ['designation' => 'Member', 'sort_order' => 3],
                ['designation' => 'Member', 'sort_order' => 4],
            ];

            foreach ($members as $index => $member) {
                BoardMember::firstOrCreate(
                    ['board_key' => $boardKey, 'sort_order' => $member['sort_order']],
                    ['name' => 'Ar. [Name]', 'image' => '', 'is_active' => true] + $member
                );
            }
        }

        $boards = [
            [
                'name' => 'Board of Management',
                'description' => 'Governs and oversees the strategic direction, events, and programs of the Institute.',
                'path' => '/management',
                'icon' => '/images/board-bom.svg',
                'sort_order' => 1,
            ],
            [
                'name' => 'Board of Architectural Education',
                'description' => 'Supports education, examinations, and member development across architecture schools.',
                'path' => '/bae',
                'icon' => '/images/board-bae.svg',
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional Affairs Board',
                'description' => 'Oversees practice registrations, CPD events, and professional conduct standards.',
                'path' => '/pab',
                'icon' => '/images/board-pab.svg',
                'sort_order' => 3,
            ],
            [
                'name' => 'Board of Architectural Publications',
                'description' => 'Manages SLIA publications, journals, and archive materials for the profession.',
                'path' => '/bap',
                'icon' => '/images/board-bap.svg',
                'sort_order' => 4,
            ],
        ];

        $boardNames = array_column($boards, 'name');
        Board::whereNotIn('name', $boardNames)->delete();

        foreach ($boards as $boardData) {
            $board = Board::firstOrNew(['name' => $boardData['name']]);
            if (!$board->exists || !$board->description) {
                $board->description = $boardData['description'];
            }
            $board->path = $boardData['path'];
            $board->icon = $boardData['icon'];
            $board->is_active = true;
            $board->sort_order = $boardData['sort_order'];
            $board->save();
        }

        $heroImages = [
            [
                'tag' => 'Featured',
                'title' => 'Sri Lanka Institute of Architects',
                'description' => 'Advancing Architecture and the Built Environment',
                'image' => '/images/hero-1.svg',
                'link' => '/',
            ],
            [
                'tag' => 'News & Events',
                'title' => 'Latest Events and Updates',
                'description' => 'Stay connected with our latest news and upcoming events',
                'image' => '/images/hero-2.svg',
                'link' => '/latest-news',
            ],
        ];

        foreach ($heroImages as $heroData) {
            HeroImage::updateOrCreate(
                ['title' => $heroData['title']],
                $heroData
            );
        }

        $eventImages = [
            'Annual Conference 2026' => '/images/event-conference.svg',
            'Design Workshop' => '/images/event-workshop.svg',
            'Member Networking Event' => '/images/event-networking.svg',
        ];

        foreach ($eventImages as $title => $image) {
            Event::where('title', $title)->update(['image' => $image]);
        }

        $newsImages = [
            'New Architecture Standards Released' => '/images/news-standards.svg',
            'Sustainable Architecture Initiative' => '/images/news-sustainable.svg',
            'Student Scholarship Program Opens' => '/images/news-scholarship.svg',
            'Online Revit Course for Architects' => '/images/news-standards.svg',
            'Trainer Practices Registration for 2024' => '/images/news-sustainable.svg',
            'Call for Entries for Annual Awards 2023/24' => '/images/news-scholarship.svg',
        ];

        foreach ($newsImages as $title => $image) {
            NewsItem::where('title', $title)->update(['image' => $image]);
        }

        $contactEntries = [
            ['category' => 'General Inquiries', 'label' => 'Secretariat', 'icon' => 'address', 'text' => 'Sri Lanka Institute of Architects, 120/7, Vidya Mawatha, Colombo 07', 'href' => '', 'sort_order' => 1],
            ['category' => 'General Inquiries', 'label' => 'Secretariat', 'icon' => 'phone', 'text' => 'Tel. 011 268 9900 Ext. 208', 'href' => '', 'sort_order' => 2],
            ['category' => 'General Inquiries', 'label' => 'Secretariat', 'icon' => 'email', 'text' => 'secretariat@architects.lk', 'href' => 'mailto:secretariat@architects.lk', 'sort_order' => 3],
            ['category' => 'General Inquiries', 'label' => 'Secretariat', 'icon' => 'web', 'text' => 'www.slia.lk', 'href' => 'https://www.slia.lk', 'sort_order' => 4],
            ['category' => 'General Inquiries', 'label' => 'Location Map', 'icon' => 'address', 'text' => 'View on Google Maps', 'href' => 'https://share.google/3jp8xNyP6lLlnfv0i', 'sort_order' => 5],
            ['category' => 'General Inquiries', 'label' => 'Honorary Secretary', 'icon' => 'email', 'text' => 'honysecretary@architects.lk', 'href' => 'mailto:honysecretary@architects.lk', 'sort_order' => 6],
            ['category' => 'Officers', 'label' => 'Information Officer', 'icon' => 'person', 'text' => 'H.M.L.H Hulangamuwa', 'href' => '', 'sort_order' => 7],
            ['category' => 'Officers', 'label' => 'Information Officer', 'icon' => 'role', 'text' => 'General Manager', 'href' => '', 'sort_order' => 8],
            ['category' => 'Officers', 'label' => 'Information Officer', 'icon' => 'phone', 'text' => '077-28205202', 'href' => '', 'sort_order' => 9],
            ['category' => 'Officers', 'label' => 'Information Officer', 'icon' => 'email', 'text' => 'secretariat@architects.lk', 'href' => 'mailto:secretariat@architects.lk', 'sort_order' => 10],
            ['category' => 'Officers', 'label' => 'Designated Officer', 'icon' => 'person', 'text' => 'Archt. Prasanna Silva', 'href' => '', 'sort_order' => 11],
            ['category' => 'Officers', 'label' => 'Designated Officer', 'icon' => 'role', 'text' => 'Senior Vice President', 'href' => '', 'sort_order' => 12],
            ['category' => 'Officers', 'label' => 'Designated Officer', 'icon' => 'phone', 'text' => '077-3470200', 'href' => '', 'sort_order' => 13],
            ['category' => 'Officers', 'label' => 'Designated Officer', 'icon' => 'email', 'text' => 'prasannasilva52@gmail.com', 'href' => 'mailto:prasannasilva52@gmail.com', 'sort_order' => 14],
            ['category' => 'Board Contacts', 'label' => 'Board of Architectural Education (BAE)', 'icon' => 'phone', 'text' => 'Tel. 011 268 9900 Ext. 208', 'href' => '', 'sort_order' => 15],
            ['category' => 'Board Contacts', 'label' => 'Board of Architectural Education (BAE)', 'icon' => 'email', 'text' => 'bae@architects.lk', 'href' => 'mailto:bae@architects.lk', 'sort_order' => 16],
            ['category' => 'Board Contacts', 'label' => 'Board of Management (BOM)', 'icon' => 'phone', 'text' => 'Tel. 072 71 64 900', 'href' => '', 'sort_order' => 17],
            ['category' => 'Board Contacts', 'label' => 'Board of Management (BOM)', 'icon' => 'email', 'text' => 'bom@architects.lk', 'href' => 'mailto:bom@architects.lk', 'sort_order' => 18],
            ['category' => 'Board Contacts', 'label' => 'Professional Affairs Board (PAB)', 'icon' => 'phone', 'text' => 'Tel. +94 767 784 696', 'href' => '', 'sort_order' => 19],
            ['category' => 'Board Contacts', 'label' => 'Professional Affairs Board (PAB)', 'icon' => 'email', 'text' => 'sliapab@architects.lk', 'href' => 'mailto:sliapab@architects.lk', 'sort_order' => 20],
            ['category' => 'Board Contacts', 'label' => 'Board of Architectural Publications (BAP)', 'icon' => 'phone', 'text' => 'Tel. 011 268 9900 Ext. 208', 'href' => '', 'sort_order' => 21],
            ['category' => 'Board Contacts', 'label' => 'Board of Architectural Publications (BAP)', 'icon' => 'email', 'text' => 'bap@architects.lk', 'href' => 'mailto:bap@architects.lk', 'sort_order' => 22],
        ];

        ContactEntry::where('category', 'Officers')
            ->whereIn('label', ['H.M.L.H Hulangamuwa', 'Archt. Prasanna Silva'])
            ->delete();

        ContactEntry::where('category', 'Board Contacts')
            ->where('sort_order', '<', 15)
            ->delete();

        foreach ($contactEntries as $entry) {
            $identity = [
                'category' => $entry['category'],
                'label' => $entry['label'],
                'icon' => $entry['icon'],
            ];

            ContactEntry::updateOrCreate(
                $identity,
                $entry + ['is_active' => true, 'is_locked' => false]
            );
        }
    }
}
