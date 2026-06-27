<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DiskonStrata;
use App\Models\Barang;

class DiskonStrataBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Wrap in transaction for safety
        DB::transaction(function () {
            
            // Group 1: MR TEH WONG NDESO SUPER BIG @12
            $group1 = [
                'items' => ['BRG001557'],
                'tiers' => [
                    ['min_qty' => 5,   'max_qty' => 9,    'dis1' => 1.25, 'dis2' => 0.00],
                    ['min_qty' => 10,  'max_qty' => 24,   'dis1' => 2.50, 'dis2' => 0.00],
                    ['min_qty' => 25,  'max_qty' => 49,   'dis1' => 3.75, 'dis2' => 0.00],
                    ['min_qty' => 50,  'max_qty' => 99,   'dis1' => 4.25, 'dis2' => 0.00],
                    ['min_qty' => 100, 'max_qty' => null, 'dis1' => 5.00, 'dis2' => 0.00],
                ]
            ];

            // Group 2: Mie/Stick/Dendeng/Balok Pedas Manis 20gr
            $group2 = [
                'items' => [
                    'BRG001549', // MIE PEDAS MANIS 20GR 6X20
                    'BRG001554', // [B] STICK PEDAS MANIS 20GR 6X20
                    'BRG001534', // [P] DENDENG PEDAS MANIS 20GR 6X20
                    'BRG001522', // [T] BALOK PEDAS MANIS 20GR 6X20
                ],
                'tiers' => [
                    ['min_qty' => 1,  'max_qty' => 2,    'dis1' => 1.00, 'dis2' => 1.00],
                    ['min_qty' => 3,  'max_qty' => 4,    'dis1' => 2.00, 'dis2' => 1.00],
                    ['min_qty' => 5,  'max_qty' => 9,    'dis1' => 3.00, 'dis2' => 1.00],
                    ['min_qty' => 10, 'max_qty' => 25,   'dis1' => 4.00, 'dis2' => 1.00],
                    ['min_qty' => 26, 'max_qty' => 49,   'dis1' => 5.00, 'dis2' => 1.00],
                    ['min_qty' => 50, 'max_qty' => null, 'dis1' => 5.00, 'dis2' => 1.00],
                ]
            ];

            // Group 3: 5 BAL/KTN up to 200 BAL/KTN
            $group3 = [
                'items' => [
                    'BRG001524', // MR JERUK NIPIS BTL @12
                    'BRG001523', // MR COCOPANDAN BTL @12
                    'BRG001788', // MR PUDING MELON
                    'BRG001704', // MR PUDING KELAPA
                    'BRG001580', // MR COCOPANDAN MANGKOK
                    'BRG001784', // MR PUDING MANGGA
                    'BRG001558', // MR MINUMAN RASA YOGURT
                    'BRG001556', // MR TEH FENTANIA JUMBO ORI @24
                    'BRG002019', // MR ES CAMPUR MANGKOK
                    'BRG001548', // MR KOPYOR MANGGA MADU BESAR
                    'BRG001546', // MR MINUMAN RASA KOPI SUSU
                    'BRG001542', // MR JERUK NIPIS MADU PRINTING
                    'BRG001540', // MR SPRITANIA CINCAU JELY MAD
                    'BRG001526', // MR KOPYOR MELON MADU BESAR
                    'BRG001192', // MR KOPYOR BUBBLE COCOPANDAN
                    'BRG001005', // MR JELY SENDOK MELON
                    'BRG000812', // MR FENTANIA JELLY ASSORTED FRUIT 12X4
                    'BRG000759', // MR ES KELAPA CINCAU
                    'BRG000566', // MR ES TELLER MANGKOK
                ],
                'tiers' => [
                    ['min_qty' => 5,   'max_qty' => 9,    'dis1' => 2.00, 'dis2' => 0.00],
                    ['min_qty' => 10,  'max_qty' => 24,   'dis1' => 3.00, 'dis2' => 0.00],
                    ['min_qty' => 25,  'max_qty' => 49,   'dis1' => 4.00, 'dis2' => 0.00],
                    ['min_qty' => 50,  'max_qty' => 99,   'dis1' => 5.00, 'dis2' => 0.00],
                    ['min_qty' => 100, 'max_qty' => 199,  'dis1' => 5.00, 'dis2' => 1.00],
                    ['min_qty' => 200, 'max_qty' => null, 'dis1' => 5.00, 'dis2' => 2.00],
                ]
            ];

            // Group 4: Big Pudding & Big Mangkok (5 KTN up to 100 KTN)
            $group4 = [
                'items' => [
                    'BRG001550', // MR PUDING STRAWBERRY BIG
                    'BRG001789', // MR PUDING MELON BIG
                    'BRG001783', // MR PUDING MANGGA BIG
                    'BRG001574', // MR ES KELAPA MUDA MANGKOK BIG
                ],
                'tiers' => [
                    ['min_qty' => 5,   'max_qty' => 9,    'dis1' => 2.00, 'dis2' => 0.00],
                    ['min_qty' => 10,  'max_qty' => 24,   'dis1' => 3.00, 'dis2' => 0.00],
                    ['min_qty' => 25,  'max_qty' => 49,   'dis1' => 4.00, 'dis2' => 0.00],
                    ['min_qty' => 50,  'max_qty' => 99,   'dis1' => 5.00, 'dis2' => 0.00],
                    ['min_qty' => 100, 'max_qty' => null, 'dis1' => 5.00, 'dis2' => 1.00],
                ]
            ];

            $groups = [$group1, $group2, $group3, $group4];

            foreach ($groups as $group) {
                foreach ($group['items'] as $kodeBarang) {
                    // Check if barang exists in the database
                    $barang = Barang::find($kodeBarang);
                    if (!$barang) {
                        $this->command->warn("Barang dengan kode {$kodeBarang} tidak ditemukan. Dilewati.");
                        continue;
                    }

                    // Avoid duplicate diskon strata header for the same barang
                    // We delete old diskon strata headers of type 'barang' for this item to ensure clean seeding
                    $existingStrataIds = DB::table('diskon_strata_barang')
                        ->where('kode_barang', $kodeBarang)
                        ->pluck('diskon_strata_id');

                    if ($existingStrataIds->isNotEmpty()) {
                        DiskonStrata::destroy($existingStrataIds);
                    }

                    // Create DiskonStrata Header
                    $header = DiskonStrata::create([
                        'nama_diskon' => 'Strata ' . $barang->nama_barang,
                        'tipe' => 'barang',
                        'berlaku_dari' => now(),
                        'berlaku_sampai' => now()->addYears(5), // Active for 5 years
                        'is_active' => true,
                    ]);

                    // Attach Barang via Pivot Table
                    $header->barangs()->sync([$kodeBarang]);

                    // Create Details/Tiers
                    foreach ($group['tiers'] as $tier) {
                        $header->details()->create([
                            'min_qty' => $tier['min_qty'],
                            'max_qty' => $tier['max_qty'],
                            'tipe_nilai' => 'persen',
                            'dis1' => $tier['dis1'],
                            'dis2' => $tier['dis2'],
                        ]);
                    }

                    $this->command->info("Berhasil membuat diskon strata untuk {$barang->nama_barang} ({$kodeBarang})");
                }
            }
        });
    }
}
