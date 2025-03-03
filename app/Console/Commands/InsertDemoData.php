<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Article\Entities\Post;
use Modules\Category\Models\Category;
use Modules\Comment\Entities\Comment;
use Modules\Tag\Entities\Tag;

class InsertDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:insert-demo-data {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert demo data for posts, categories, tags, and comments. --fresh option will truncate the tables.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Auth::loginUsingId(1);

        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->truncate_tables();
        }

        $this->insert_demo_data();
    }

    public function insert_demo_data()
    {
        /**
         * Categories.
         */
        $this->info('Inserting Categories');
        Category::factory()->count(5)->create();

        /**
         * Tags.
         */
        $this->info('Inserting Tags');
        Tag::factory()->count(10)->create();

        /**
         * Posts.
         */
        $this->info('Inserting Posts');
        Post::factory()->count(25)->create()->each(function ($post) {
            $post->tags()->attach(
                Tag::inRandomOrder()->limit(rand(5, 10))->pluck('id')->toArray()
            );
        });

        /**
         * Comments.
         */
        $this->info('Inserting Comments');
        Comment::factory()->count(25)->create();

        $this->newLine(2);
        $this->info('-- Completed --');
        $this->newLine();
    }

    public function truncate_tables()
    {
        $tables_list = [
            'posts',
            'categories',
            'tags',
            'comments',
            'activity_log',
        ];

        if ($this->confirm('Database tables (posts, categories, tags, comments) will become empty. Confirm truncate tables?')) {
            // Disable foreign key checks!
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables_list as $row) {
                $table_name = DB::getTablePrefix().''.$row;

                $this->info("Truncate Table: $table_name");

                DB::table($table_name)->truncate();
            }

            // Enable foreign key checks!
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
