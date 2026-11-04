<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Onsite Parking Locations
    |--------------------------------------------------------------------------
    |
    | Used as fallbacks when the database table `lokasis` is empty or being
    | seeded.
    |
    */
    'default_locations' => [
        [
            'id' => 1,
            'kode' => 1,
            'name' => 'True Blue',
            'nama_lokasi' => 'True Blue',
            'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            'keterangan_alamat' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            'batas_waktu_henti' => 60,
        ],
        [
            'id' => 2,
            'kode' => 2,
            'name' => 'Gelael Tebet',
            'nama_lokasi' => 'Gelael Tebet',
            'address' => 'Jl. Tebet Barat Dalam Raya No.1, RT.1/RW.4, Tebet Barat, Jakarta Selatan, DKI Jakarta 12810',
            'keterangan_alamat' => 'Jl. Tebet Barat Dalam Raya No.1, RT.1/RW.4, Tebet Barat, Jakarta Selatan, DKI Jakarta 12810',
            'batas_waktu_henti' => 120,
        ],
        [
            'id' => 3,
            'kode' => 3,
            'name' => 'Intermark BSD',
            'nama_lokasi' => 'Intermark BSD',
            'address' => 'Jl. Lingkar Timur No.9, Rawa Mekar Jaya, Serpong, Kota Tangerang Selatan, Banten 15310',
            'keterangan_alamat' => 'Jl. Lingkar Timur No.9, Rawa Mekar Jaya, Serpong, Kota Tangerang Selatan, Banten 15310',
            'batas_waktu_henti' => 45,
        ],
        [
            'id' => 4,
            'kode' => 4,
            'name' => 'Ciracas Center',
            'nama_lokasi' => 'Ciracas Center',
            'address' => 'Jl. Raya Ciracas No.12, RT.6/RW.3, Ciracas, Kota Jakarta Timur, DKI Jakarta 13740',
            'keterangan_alamat' => 'Jl. Raya Ciracas No.12, RT.6/RW.3, Ciracas, Kota Jakarta Timur, DKI Jakarta 13740',
            'batas_waktu_henti' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre-configured Network Devices
    |--------------------------------------------------------------------------
    |
    | Known network devices and mock monitoring status mapped by location ID.
    |
    */
    'network_devices' => [
        1 => [
            'jaringan' => '192.168.10.1 / Fiber Biznet 100Mbps',
            'downtime' => '0m (0%)',
            'status' => 'Online',
            'update' => 'Baru saja (26/10/2026 11:20)',
            'history' => [
                ['date' => '26/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '25/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '24/10/2026', 'downtime' => '5m', 'status' => 'Gangguan Singkat'],
                ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
            ],
        ],
        2 => [
            'jaringan' => '192.168.20.1 / Indihome 50Mbps',
            'downtime' => '25m (1.7%)',
            'status' => 'Gangguan',
            'update' => '5 menit lalu (26/10/2026 11:15)',
            'history' => [
                ['date' => '26/10/2026', 'downtime' => '25m', 'status' => 'Down'],
                ['date' => '25/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '24/10/2026', 'downtime' => '12m', 'status' => 'RTO Berkala'],
                ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
            ],
        ],
        3 => [
            'jaringan' => '192.168.30.1 / MyRepublic 100Mbps',
            'downtime' => '0m (0%)',
            'status' => 'Online',
            'update' => '1 menit lalu (26/10/2026 11:19)',
            'history' => [
                ['date' => '26/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '25/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '24/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
            ],
        ],
        4 => [
            'jaringan' => '192.168.40.1 / FirstMedia 75Mbps',
            'downtime' => '2h 10m (9.0%)',
            'status' => 'Offline',
            'update' => '15 menit lalu (26/10/2026 11:05)',
            'history' => [
                ['date' => '26/10/2026', 'downtime' => '2h 10m', 'status' => 'Offline'],
                ['date' => '25/10/2026', 'downtime' => '45m', 'status' => 'Down'],
                ['date' => '24/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
            ],
        ],
    ],
];
