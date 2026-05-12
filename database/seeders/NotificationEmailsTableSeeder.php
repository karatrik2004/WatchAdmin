<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationEmailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        $emails = [
            // Deal created
            ['type' => 'deal_created', 'email' => 'mariang@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'deal_created', 'email' => 'divpreetk@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'deal_created', 'email' => 'eliezerk@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'deal_created', 'email' => 'sas@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'deal_created', 'email' => 'tinal@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'deal_created', 'email' => 'vaishnavin@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'deal_created', 'email' => 'bhmhtrading@gmail.com', 'created_at' => $now, 'updated_at' => $now],
            // Sales invoice created
            ['type' => 'sales_invoice_created', 'email' => 'mariang@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'sales_invoice_created', 'email' => 'divpreetk@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'sales_invoice_created', 'email' => 'tinal@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'sales_invoice_created', 'email' => 'vaishnavin@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'sales_invoice_created', 'email' => 'bhmhtrading@gmail.com', 'created_at' => $now, 'updated_at' => $now],
            // Deal reviewed and ready for funding
            ['type' => 'deal_reviewed_ready_for_funding', 'email' => 'mariang@bcig.com.au', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('notification_emails')->insert($emails);
    }
}
