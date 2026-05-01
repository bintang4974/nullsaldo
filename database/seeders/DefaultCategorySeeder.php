<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default categories for all existing users
        foreach (User::all() as $user) {
            self::seedForUser($user);
        }
    }

    /**
     * Buat kategori default untuk user tertentu
     */
    public static function seedForUser(User $user): void
    {
        $defaultCategories = [
            // Kategori Expense
            [
                'name' => 'Makan & Minum',
                'icon' => 'utensils',
                'color' => '#F59E0B',
                'type' => 'expense',
            ],
            [
                'name' => 'Transport',
                'icon' => 'car',
                'color' => '#3B82F6',
                'type' => 'expense',
            ],
            [
                'name' => 'Belanja',
                'icon' => 'shopping-bag',
                'color' => '#EC4899',
                'type' => 'expense',
            ],
            [
                'name' => 'Hiburan',
                'icon' => 'film',
                'color' => '#8B5CF6',
                'type' => 'expense',
            ],
            [
                'name' => 'Kos/Sewa',
                'icon' => 'home',
                'color' => '#10B981',
                'type' => 'expense',
            ],
            [
                'name' => 'Internet & Pulsa',
                'icon' => 'wifi',
                'color' => '#06B6D4',
                'type' => 'expense',
            ],
            [
                'name' => 'Kesehatan',
                'icon' => 'heart',
                'color' => '#EF4444',
                'type' => 'expense',
            ],
            [
                'name' => 'Pendidikan',
                'icon' => 'book',
                'color' => '#F97316',
                'type' => 'expense',
            ],
            [
                'name' => 'Lainnya',
                'icon' => 'more-horizontal',
                'color' => '#6B7280',
                'type' => 'expense',
            ],

            // Kategori Income
            [
                'name' => 'Gaji',
                'icon' => 'briefcase',
                'color' => '#10B981',
                'type' => 'income',
            ],
            [
                'name' => 'Uang Saku',
                'icon' => 'gift',
                'color' => '#F59E0B',
                'type' => 'income',
            ],
            [
                'name' => 'Bonus',
                'icon' => 'award',
                'color' => '#8B5CF6',
                'type' => 'income',
            ],
            [
                'name' => 'Freelance',
                'icon' => 'code',
                'color' => '#3B82F6',
                'type' => 'income',
            ],
        ];

        foreach ($defaultCategories as $category) {
            $user->categories()->create($category);
        }
    }
}
