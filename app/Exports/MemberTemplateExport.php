<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Faker\Factory as Faker;

class MemberTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $jumlahData;

    // Constructor: Default 10 data, tapi bisa diubah saat dipanggil
    public function __construct(int $jumlahData = 10)
    {
        $this->jumlahData = $jumlahData;
    }
    
    public function title(): string
    {
        return 'Template Import Anggota';
    }

    public function headings(): array
    {
        return [
            'NO',                   // Kolom A
            'NBM',                  // Kolom B (Wajib Unik)
            'NAMA LENGKAP',         // Kolom C (Wajib)
            'NIK',                  // Kolom D
            'TANGGAL LAHIR',        // Kolom E (Format: YYYY-MM-DD)
            'TEMPAT LAHIR',
            'JENIS KELAMIN (L/P)',  // Kolom F
            'NO HP',                // Kolom G
            'ALAMAT DOMISILI',      // Kolom H
            'PENDIDIKAN TERAKHIR',  // Kolom I (SD, SMP, SMA, S1, S2, dll)
            'PEKERJAAN'             // Kolom J
        ];
    }

    public function array(): array
    {
        // Gunakan locale Indonesia agar nama & alamat terlihat nyata
        $faker = Faker::create('id_ID'); 
        $data = [];

        for ($i = 1; $i <= $this->jumlahData; $i++) {
            $gender = $faker->randomElement(['L', 'P']);
            // Trik: Sesuaikan nama dengan gender (opsional, biar lebih real)
            $name = $gender === 'L' ? $faker->name('male') : $faker->name('female');

            $data[] = [
                $i,                                             // NO
                $faker->unique()->numerify('1#######'),         // NBM (7-8 Digit)
                $name,                                          // NAMA
                $faker->nik(),                                  // NIK (Faker ID punya generator NIK valid)
                $faker->date('Y-m-d', '-20 years'),             // TGL LAHIR (Minimal 20 thn lalu)
                $faker->city(),                                 // TEMPAT LAHIR
                $gender,                                        // JK
                $faker->numerify('08##########'),               // HP
                $faker->address(),                              // ALAMAT
                $faker->randomElement(['SMA', 'DIPLOMA', 'S1', 'S2', 'S3']), // PENDIDIKAN
                $faker->jobTitle()                              // PEKERJAAN
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header (Baris 1) agar terlihat seperti Template Resmi
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Putih
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF059669'], // Warna Emerald-600 (Sesuai tema aplikasi)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Set Format Text untuk kolom NIK dan HP agar tidak berubah jadi scientific number (E+12)
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('@'); // NBM Text
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('@'); // NIK Text
        $sheet->getStyle('G')->getNumberFormat()->setFormatCode('@'); // HP Text
        
        // Validasi Dropdown Sederhana (Data Validation) untuk Jenis Kelamin
        $validation = $sheet->getCell('F2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"L,P"'); // List Dropdown Excel

        // Terapkan validasi ke baris 2 sampai 1000
        for ($i = 2; $i <= 1000; $i++) {
            $sheet->getCell("F$i")->setDataValidation(clone $validation);
        }

        return [];
    }
}