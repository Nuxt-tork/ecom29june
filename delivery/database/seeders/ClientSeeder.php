<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $secretPlain = Str::random(32); // raw secret to give to Ecommerce1
        $encryptedSecret = Crypt::encryptString($secretPlain);

        Client::create([
            'name' => 'Ecommerce1',
            'url' => 'http://127.0.0.1:1001',
            'secret' => $encryptedSecret,
            'secret_type' => 'sandbox',
        ]);

        echo "\n--- CLIENT SECRET FOR ECOMMERCE1 ---\n";
        echo "Store this in Ecommerce1 .env:\n";
        echo "FOODPANDA_CLIENT_SECRET=" . $secretPlain . "\n";
    }
}
