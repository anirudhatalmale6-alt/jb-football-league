<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Group;
use App\Models\MatchEvent;
use App\Models\MatchGame;
use App\Models\MatchLineup;
use App\Models\Official;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // 1. Users (created without team_id first)
        // ──────────────────────────────────────────────
        $superAdmin = User::create([
            'name'     => 'JBFA Admin',
            'email'    => 'admin@jbfa.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
            'team_id'  => null,
        ]);

        $leagueAdmin = User::create([
            'name'     => 'Liga Admin',
            'email'    => 'league@jbfa.com',
            'password' => Hash::make('password'),
            'role'     => 'league_admin',
            'team_id'  => null,
        ]);

        $teamManager1 = User::create([
            'name'     => 'Ahmad Razak',
            'email'    => 'ahmad@team1.com',
            'password' => Hash::make('password'),
            'role'     => 'team_manager',
            'team_id'  => null,
        ]);

        $teamManager2 = User::create([
            'name'     => 'Lim Wei Keat',
            'email'    => 'lim@team2.com',
            'password' => Hash::make('password'),
            'role'     => 'team_manager',
            'team_id'  => null,
        ]);

        // ──────────────────────────────────────────────
        // 2. Competitions
        // ──────────────────────────────────────────────

        // (a) JBFA Super League 2026 — round robin, 10 teams, no groups
        $superLeague = Competition::create([
            'name'          => 'JBFA Super League 2026',
            'season'        => '2026',
            'type'          => 'league',
            'status'        => 'active',
            'start_date'    => '2026-01-15',
            'end_date'      => '2026-06-30',
            'description'   => 'Top-tier league of the Johor Bahru Football Association featuring the best clubs in the state.',
            'max_players'   => 25,
            'max_officials' => 7,
        ]);

        // (b) JBFA Premier League 2026 — 2 groups, 12 teams (6 per group)
        $premierLeague = Competition::create([
            'name'          => 'JBFA Premier League 2026',
            'season'        => '2026',
            'type'          => 'league',
            'status'        => 'active',
            'start_date'    => '2026-02-01',
            'end_date'      => '2026-07-15',
            'description'   => 'Second-tier league competition divided into two groups.',
            'max_players'   => 25,
            'max_officials' => 7,
        ]);

        // (c) JBFA Divisyen League 2026 — 6 groups, 30 teams (5 per group)
        $divisyenLeague = Competition::create([
            'name'          => 'JBFA Divisyen League 2026',
            'season'        => '2026',
            'type'          => 'league',
            'status'        => 'active',
            'start_date'    => '2026-03-01',
            'end_date'      => '2026-08-30',
            'description'   => 'Grassroots divisional league with six regional groups across Johor.',
            'max_players'   => 25,
            'max_officials' => 7,
        ]);

        // ──────────────────────────────────────────────
        // 3. Groups
        // ──────────────────────────────────────────────

        // Premier League — 2 groups
        $premierGroupA = Group::create(['competition_id' => $premierLeague->id, 'name' => 'Kumpulan A', 'order' => 1]);
        $premierGroupB = Group::create(['competition_id' => $premierLeague->id, 'name' => 'Kumpulan B', 'order' => 2]);

        // Divisyen League — 6 groups
        $divisyenGroups = [];
        for ($g = 1; $g <= 6; $g++) {
            $divisyenGroups[] = Group::create([
                'competition_id' => $divisyenLeague->id,
                'name'           => 'Kumpulan ' . $g,
                'order'          => $g,
            ]);
        }

        // ──────────────────────────────────────────────
        // 4. Teams
        // ──────────────────────────────────────────────

        // --- Super League (10 teams, no group) ---
        $superLeagueTeamData = [
            ['name' => 'Johor Bahru FC',       'short_name' => 'JB FC',       'manager_name' => 'Ahmad Razak',         'contact_email' => 'info@johorbahrufc.com'],
            ['name' => 'Pasir Gudang United',   'short_name' => 'PG United',   'manager_name' => 'Lim Wei Keat',        'contact_email' => 'info@pgunited.com'],
            ['name' => 'Skudai City FC',        'short_name' => 'Skudai FC',   'manager_name' => 'Mohd Faizal Ismail',  'contact_email' => 'admin@skudaicityfc.com'],
            ['name' => 'Kulai Rangers',         'short_name' => 'Kulai RG',    'manager_name' => 'Tan Chee Hong',       'contact_email' => 'info@kulairangers.com'],
            ['name' => 'Kota Tinggi Warriors',  'short_name' => 'KT Warriors', 'manager_name' => 'Syed Amir Hamzah',    'contact_email' => 'info@ktwarriors.com'],
            ['name' => 'Pontian FC',            'short_name' => 'Pontian FC',  'manager_name' => 'R. Gunasegaran',      'contact_email' => 'office@pontianfc.com'],
            ['name' => 'Segamat United',        'short_name' => 'Segamat UT',  'manager_name' => 'Mohd Zulkifli Yusof', 'contact_email' => 'info@segamatunited.com'],
            ['name' => 'Mersing Strikers',      'short_name' => 'Mersing SK',  'manager_name' => 'Lee Chong Wei',       'contact_email' => 'info@mersingstrikers.com'],
            ['name' => 'Kluang City FC',        'short_name' => 'Kluang FC',   'manager_name' => 'K. Thanaraj',         'contact_email' => 'admin@kluangcityfc.com'],
            ['name' => 'Muar United',           'short_name' => 'Muar UT',     'manager_name' => 'Kamal Bahari',        'contact_email' => 'info@muarunited.com'],
        ];

        $superLeagueTeams = [];
        foreach ($superLeagueTeamData as $data) {
            $superLeagueTeams[] = Team::create(array_merge($data, [
                'competition_id' => $superLeague->id,
                'group_id'       => null,
                'status'         => 'approved',
            ]));
        }

        // --- Premier League (12 teams, 6 per group) ---
        $premierTeamData = [
            // Group A
            ['name' => 'Batu Pahat FC',        'short_name' => 'BP FC',       'manager_name' => 'Ismail Sabri',          'contact_email' => 'info@batupahatfc.com',    'group_id' => $premierGroupA->id],
            ['name' => 'Tangkak Rangers',       'short_name' => 'Tangkak RG',  'manager_name' => 'Ong Chin Huat',         'contact_email' => 'info@tangkakrangers.com', 'group_id' => $premierGroupA->id],
            ['name' => 'Simpang Renggam FC',    'short_name' => 'SR FC',       'manager_name' => 'Mohd Ridzuan Nordin',   'contact_email' => 'info@srfc.com',           'group_id' => $premierGroupA->id],
            ['name' => 'Gelang Patah United',   'short_name' => 'GP United',   'manager_name' => 'Wong Siu Lun',          'contact_email' => 'info@gpunited.com',       'group_id' => $premierGroupA->id],
            ['name' => 'Nusajaya City',         'short_name' => 'Nusajaya CT', 'manager_name' => 'Ahmad Nazmi Kadir',     'contact_email' => 'info@nusajayacity.com',   'group_id' => $premierGroupA->id],
            ['name' => 'Permas Jaya FC',        'short_name' => 'PJ FC',       'manager_name' => 'S. Kuganesh',           'contact_email' => 'info@permasjayafc.com',   'group_id' => $premierGroupA->id],
            // Group B
            ['name' => 'Larkin United',         'short_name' => 'Larkin UT',   'manager_name' => 'Mohd Hafiz Rahman',     'contact_email' => 'info@larkinunited.com',   'group_id' => $premierGroupB->id],
            ['name' => 'Senai Strikers',        'short_name' => 'Senai SK',    'manager_name' => 'Tan Boon Keat',         'contact_email' => 'info@senaistrikers.com',  'group_id' => $premierGroupB->id],
            ['name' => 'Ulu Tiram FC',          'short_name' => 'UT FC',       'manager_name' => 'Roslan Mat Daud',       'contact_email' => 'info@ulutiramfc.com',     'group_id' => $premierGroupB->id],
            ['name' => 'Iskandar Puteri FC',    'short_name' => 'IP FC',       'manager_name' => 'Muthu Kannan',          'contact_email' => 'info@ipfc.com',           'group_id' => $premierGroupB->id],
            ['name' => 'Masai City FC',         'short_name' => 'Masai FC',    'manager_name' => 'Chong Kok Wah',         'contact_email' => 'info@masaicityfc.com',    'group_id' => $premierGroupB->id],
            ['name' => 'Tebrau Rangers',        'short_name' => 'Tebrau RG',   'manager_name' => 'Hasbullah Awang',       'contact_email' => 'info@tebraurangers.com',  'group_id' => $premierGroupB->id],
        ];

        foreach ($premierTeamData as $data) {
            $groupId = $data['group_id'];
            unset($data['group_id']);
            Team::create(array_merge($data, [
                'competition_id' => $premierLeague->id,
                'group_id'       => $groupId,
                'status'         => 'approved',
            ]));
        }

        // --- Divisyen League (30 teams, 5 per group) ---
        $divisyenTeamData = [
            // Kumpulan 1
            ['name' => 'Taman Perling FC',     'short_name' => 'TP FC',       'manager_name' => 'Mohd Azlan Yusof',     'contact_email' => 'info@tpfc.com',             'group' => 0],
            ['name' => 'Johor Jaya United',    'short_name' => 'JJ United',   'manager_name' => 'Lau Kah Fai',          'contact_email' => 'info@jjunited.com',         'group' => 0],
            ['name' => 'Tampoi City',          'short_name' => 'Tampoi CT',   'manager_name' => 'Ahmad Fadzil Hamid',   'contact_email' => 'info@tampoicity.com',       'group' => 0],
            ['name' => 'Kangkar Pulai FC',     'short_name' => 'KP FC',       'manager_name' => 'Ng Jia Hao',           'contact_email' => 'info@kpfc.com',             'group' => 0],
            ['name' => 'Danga Bay FC',         'short_name' => 'DB FC',       'manager_name' => 'V. Sutharsan',          'contact_email' => 'info@dangabayfc.com',       'group' => 0],
            // Kumpulan 2
            ['name' => 'Tanjung Puteri FC',    'short_name' => 'TJ Puteri',   'manager_name' => 'Mohd Safwan Ali',      'contact_email' => 'info@tjputerifc.com',       'group' => 1],
            ['name' => 'Stulang Laut United',  'short_name' => 'SL United',   'manager_name' => 'Chan Wai Kit',         'contact_email' => 'info@slunited.com',         'group' => 1],
            ['name' => 'Majidee FC',           'short_name' => 'Majidee FC',  'manager_name' => 'P. Navin Kumar',       'contact_email' => 'info@majideefc.com',        'group' => 1],
            ['name' => 'Plentong Warriors',    'short_name' => 'Plentong WR', 'manager_name' => 'Mohd Khairul Anwar',   'contact_email' => 'info@plentongwarriors.com', 'group' => 1],
            ['name' => 'Perling United',       'short_name' => 'Perling UT',  'manager_name' => 'Goh Yong Seng',        'contact_email' => 'info@perlingunited.com',    'group' => 1],
            // Kumpulan 3
            ['name' => 'Skudai Utama FC',      'short_name' => 'SU FC',       'manager_name' => 'Mohd Aizat Wahab',     'contact_email' => 'info@skudaiutamafc.com',    'group' => 2],
            ['name' => 'Taman Universiti FC',  'short_name' => 'TU FC',       'manager_name' => 'Koh Boon Huat',        'contact_email' => 'info@tufc.com',             'group' => 2],
            ['name' => 'Bukit Indah City',     'short_name' => 'BI City',     'manager_name' => 'Ahmad Luqman Hakim',   'contact_email' => 'info@bukitindahcity.com',   'group' => 2],
            ['name' => 'Setia Tropika FC',     'short_name' => 'ST FC',       'manager_name' => 'T. Saravanan',         'contact_email' => 'info@setiatropikafc.com',   'group' => 2],
            ['name' => 'Horizon Hills FC',     'short_name' => 'HH FC',       'manager_name' => 'Yap Jian Ming',        'contact_email' => 'info@hhfc.com',             'group' => 2],
            // Kumpulan 4
            ['name' => 'Taman Daya FC',        'short_name' => 'TD FC',       'manager_name' => 'Mohd Zikri Azman',     'contact_email' => 'info@tamandayafc.com',      'group' => 3],
            ['name' => 'Pasir Pelangi United', 'short_name' => 'PP United',   'manager_name' => 'Cheah Hong Leong',     'contact_email' => 'info@ppunited.com',         'group' => 3],
            ['name' => 'Mount Austin FC',      'short_name' => 'MA FC',       'manager_name' => 'R. Praveen',           'contact_email' => 'info@mountaustinfc.com',    'group' => 3],
            ['name' => 'Taman Molek FC',       'short_name' => 'TM FC',       'manager_name' => 'Ahmad Fitri Hassan',   'contact_email' => 'info@tamanmolekfc.com',     'group' => 3],
            ['name' => 'Pandan Perdana FC',    'short_name' => 'PP FC',       'manager_name' => 'Foo Kok Wai',          'contact_email' => 'info@pandanperdanafc.com',  'group' => 3],
            // Kumpulan 5
            ['name' => 'Taman Sentosa FC',     'short_name' => 'TS FC',       'manager_name' => 'Mohd Afiq Roslan',     'contact_email' => 'info@tamansentosafc.com',   'group' => 4],
            ['name' => 'Sri Stulang United',   'short_name' => 'SS United',   'manager_name' => 'Sim Wei Chong',        'contact_email' => 'info@sristulang.com',       'group' => 4],
            ['name' => 'Bandar Baru Uda FC',   'short_name' => 'BBU FC',      'manager_name' => 'A. Vikneswaran',       'contact_email' => 'info@bbufc.com',            'group' => 4],
            ['name' => 'Taman Pelangi FC',     'short_name' => 'TPL FC',      'manager_name' => 'Mohd Farhan Idris',    'contact_email' => 'info@tamanpelangifc.com',   'group' => 4],
            ['name' => 'Dato Onn United',      'short_name' => 'DO United',   'manager_name' => 'Teo Wei Lun',          'contact_email' => 'info@datoonunited.com',     'group' => 4],
            // Kumpulan 6
            ['name' => 'Taman Sutera FC',      'short_name' => 'TSU FC',      'manager_name' => 'Ahmad Kamal Noor',     'contact_email' => 'info@tamansuterafc.com',    'group' => 5],
            ['name' => 'Setia Indah FC',       'short_name' => 'SI FC',       'manager_name' => 'L. Kogulraj',          'contact_email' => 'info@setiaindahfc.com',     'group' => 5],
            ['name' => 'Taman Mutiara FC',     'short_name' => 'TMU FC',      'manager_name' => 'Mohd Izzuddin Shah',   'contact_email' => 'info@tamanmutiarafc.com',   'group' => 5],
            ['name' => 'Sri Pulai Perdana FC', 'short_name' => 'SPP FC',      'manager_name' => 'N. Harindran',         'contact_email' => 'info@sripulaifc.com',       'group' => 5],
            ['name' => 'Bandar Putra FC',      'short_name' => 'BP FC',       'manager_name' => 'Ong Wei Ming',         'contact_email' => 'info@bandarputrafc.com',    'group' => 5],
        ];

        foreach ($divisyenTeamData as $data) {
            $groupIndex = $data['group'];
            unset($data['group']);
            Team::create(array_merge($data, [
                'competition_id' => $divisyenLeague->id,
                'group_id'       => $divisyenGroups[$groupIndex]->id,
                'status'         => 'approved',
            ]));
        }

        // ──────────────────────────────────────────────
        // 5. Assign team managers to teams 1 and 2
        // ──────────────────────────────────────────────
        $teamManager1->update(['team_id' => $superLeagueTeams[0]->id]);
        $teamManager2->update(['team_id' => $superLeagueTeams[1]->id]);

        // ──────────────────────────────────────────────
        // 6. Players (15 per Super League team = 150 total)
        // ──────────────────────────────────────────────
        $namePool = [
            // Malay names
            'Muhammad Aiman Hakim', 'Muhammad Haziq Nabil', 'Ahmad Firdaus Shah', 'Mohd Syazwan Rosli', 'Mohd Hafiz Kamaruzaman',
            'Muhammad Arif Budiman', 'Ahmad Zulkifli Razak', 'Mohd Farid Iskandar', 'Muhammad Irfan Hakimi', 'Ahmad Danial Ashraf',
            'Mohd Azlan Bakar', 'Muhammad Nabil Fikri', 'Ahmad Shahrul Nizam', 'Mohd Izzuddin Azhar', 'Muhammad Amirul Aidil',
            'Ahmad Fadzil Ibrahim', 'Mohd Ridzuan Nasir', 'Muhammad Hakim Zainuddin', 'Ahmad Luqman Hakim', 'Mohd Khairul Fahmi',
            'Muhammad Aidil Shafiq', 'Ahmad Nazmi Faiz', 'Mohd Azri Mahmud', 'Muhammad Farhan Zulkarnain', 'Ahmad Kamal Ariffin',
            'Mohd Safwan Baharuddin', 'Muhammad Zikri Alias', 'Ahmad Fitri Hussin', 'Mohd Aizat Amri', 'Muhammad Afiq Izham',
            'Muhammad Alif Imran', 'Ahmad Syahmi Redzuan', 'Mohd Taufiq Hidayat', 'Muhammad Aqil Mirza', 'Ahmad Faiz Subri',
            'Mohd Adib Azfar', 'Muhammad Haikal Anuar', 'Ahmad Rizal Ghazali', 'Mohd Aiman Zakaria', 'Muhammad Ashraf Ismail',
            'Ahmad Muaz Othman', 'Mohd Fikri Hasan', 'Muhammad Najib Razali', 'Ahmad Hazim Latif', 'Mohd Shahril Ishak',
            'Muhammad Naim Sulaiman', 'Ahmad Fadhil Hamdan', 'Mohd Ikmal Harun', 'Muhammad Faris Azmi', 'Ahmad Izzat Kadir',
            // Chinese names
            'Lee Zhi Wei', 'Tan Jun Hao', 'Lim Kai Xiang', 'Ong Wei Ming', 'Ng Jia Hao',
            'Wong Chee Keong', 'Chan Wai Kit', 'Goh Yong Seng', 'Lau Kah Fai', 'Koh Boon Huat',
            'Teo Wei Lun', 'Yap Jian Ming', 'Cheah Hong Leong', 'Foo Kok Wai', 'Sim Wei Chong',
            'Tan Kah Meng', 'Lee Wei Sheng', 'Lim Chee Keong', 'Ong Boon Hock', 'Ng Wai Leong',
            'Wong Jian Wen', 'Chan Kok Hoong', 'Goh Chee Beng', 'Lau Wei Jie', 'Koh Jun Heng',
            'Teo Choon Kiat', 'Yap Kok Leong', 'Cheah Wei Shen', 'Foo Jun Wei', 'Sim Boon Tat',
            // Indian names
            'K. Thanaraj', 'S. Kuganesh', 'R. Praveen Kumar', 'M. Dinesh Kumar', 'T. Saravanan',
            'A. Vikneswaran', 'P. Navin Kumar', 'V. Sutharsan', 'L. Kogulraj', 'N. Harindran',
            'S. Mahendran', 'K. Ravichandran', 'R. Karthigeyan', 'M. Suresh Babu', 'T. Ganesh Kumar',
            'A. Balasundaram', 'P. Thinagaran', 'V. Loganathan', 'L. Murugan', 'N. Arumugam',
            // East Malaysian & mixed names
            'Maximus Matin', 'Ronny Harun', 'Rickie Bujang', 'Bobby Gonzaga', 'Jerry Mawieh',
            'Kenny Pallraj', 'Shahrel Fikri', 'Akhyar Rashid', 'Syafiq Ahmad', 'Safawi Rasid',
            'Afiq Fazail', 'Syamer Kutty', 'Corbin Ong', 'Quentin Cheng', 'Dominic Tan',
            'Matthew Davies', 'Gary Steven', 'Baddrol Bakhtiar', 'Norshahrul Idlan', 'Farizal Marlias',
            'Khairulazhan Khalid', 'Hazwan Bakri', 'Shahrul Saad', 'Syahmi Safari', 'Aidil Zafuan',
            'Azammuddin Akil', 'Irfan Zakaria', 'Wan Zack Haikal', 'Luqman Hakim Shamsudin', 'Nik Akif Syahiran',
            'Muhammad Safawi', 'Ahmad Tasnim Fitri', 'Mohd Aidil Zafuan', 'Muhammad Nazmi Faiz', 'Ahmad Hazwan Radzuan',
            'Mohd Faisal Halim', 'Muhammad Dion Lim', 'Ahmad Haziq Azman', 'Mohd Hadi Fayyadh', 'Muhammad Adam Nor Azlin',
            'Arif Aiman Hanapi', 'Syahrian Abimanyu', 'Mohamad Faisal Abdul Razak', 'Darren Lok', 'Stuart Wark',
            'La\'Vere Corbin-Ong', 'Brendan Gan', 'Junior Eldstal', 'Natxo Insa', 'Leandro Velasquez',
        ];

        shuffle($namePool);

        $positionTemplate = [
            'goalkeeper', 'goalkeeper',
            'defender', 'defender', 'defender', 'defender',
            'midfielder', 'midfielder', 'midfielder', 'midfielder', 'midfielder',
            'forward', 'forward', 'forward', 'forward',
        ];

        $allPlayers = [];
        $nameIndex = 0;

        foreach ($superLeagueTeams as $ti => $team) {
            $teamPlayers = [];
            for ($i = 0; $i < 15; $i++) {
                $dob = Carbon::createFromDate(rand(1990, 2004), rand(1, 12), rand(1, 28));
                $player = Player::create([
                    'team_id'       => $team->id,
                    'name'          => $namePool[$nameIndex],
                    'jersey_number' => $i + 1,
                    'position'      => $positionTemplate[$i],
                    'date_of_birth' => $dob->format('Y-m-d'),
                    'nationality'   => 'Malaysian',
                    'status'        => 'active',
                ]);
                $teamPlayers[] = $player;
                $nameIndex++;
            }
            $allPlayers[$ti] = $teamPlayers;
        }

        // ──────────────────────────────────────────────
        // 7. Officials (3 per Super League team)
        // ──────────────────────────────────────────────
        $officialNames = [
            ['Mohd Tarmizi Ali',   'Lee Hock Seng',     'Saraswathi Devi'],
            ['Kamal Bahari',       'Chong Kok Wah',     'Priya Nair'],
            ['Zainol Abidin',      'Tan Boon Keat',     'Rajesh Menon'],
            ['Ismail Sabri',       'Ong Chin Huat',     'Muthu Kannan'],
            ['Roslan Mat Daud',    'Wong Siu Lun',      'Bala Subramaniam'],
            ['Hasbullah Awang',    'Lim Teik Bee',      'Anand Krishnan'],
            ['Mohd Nor Ibrahim',   'Yap Chee Seng',     'Ravi Chandran'],
            ['Ahmad Tajuddin',     'Foo Wai Meng',      'Siti Aminah'],
            ['Zainal Abidin Mat',  'Goh Choon Keat',    'K. Selvarajah'],
            ['Mohd Razali Hassan', 'Lim Boon Chong',    'N. Balamurugan'],
        ];
        $officialRoles = ['Head Coach', 'Assistant Coach', 'Team Physio'];

        foreach ($superLeagueTeams as $ti => $team) {
            for ($i = 0; $i < 3; $i++) {
                Official::create([
                    'team_id'       => $team->id,
                    'name'          => $officialNames[$ti][$i],
                    'role'          => $officialRoles[$i],
                    'contact_phone' => '01' . rand(0, 9) . '-' . rand(1000000, 9999999),
                ]);
            }
        }

        // ──────────────────────────────────────────────
        // 8. Matches (Super League only)
        // ──────────────────────────────────────────────
        $venues = [
            'Stadium Sultan Ibrahim, Iskandar Puteri',
            'Stadium Pasir Gudang',
            'Kompleks Sukan Skudai',
            'Stadium Kulai',
            'Stadium Kota Tinggi',
            'Stadium Pontian',
            'Stadium Segamat',
            'Stadium Mersing',
            'Stadium Kluang',
            'Stadium Muar',
        ];

        $referees = [
            'Nazmi Nasaruddin',
            'Ahmad Yazid Yasin',
            'Suresh Ramalingam',
            'Mohd Amirul Izwan',
            'Sivakumar Kandasamy',
        ];

        // 5 completed matches with specified scores
        $completedMatches = [
            [0, 1, 1, 0, 1, 0, '2026-02-01 20:00:00'],  // JB FC 1-0 PG United
            [2, 3, 2, 1, 1, 2, '2026-02-01 16:00:00'],  // Skudai City 2-1 Kulai Rangers
            [4, 5, 0, 0, 1, 4, '2026-02-02 20:00:00'],  // KT Warriors 0-0 Pontian FC
            [6, 7, 3, 2, 2, 6, '2026-02-15 20:00:00'],  // Segamat United 3-2 Mersing Strikers
            [8, 9, 1, 1, 2, 8, '2026-02-15 16:00:00'],  // Kluang City 1-1 Muar United
        ];

        // 3 scheduled matches
        $scheduledMatches = [
            [0, 2, 0, 0, 3, 0, '2026-06-14 20:00:00'],  // JB FC vs Skudai City
            [1, 4, 0, 0, 3, 1, '2026-06-14 16:00:00'],  // PG United vs KT Warriors
            [3, 5, 0, 0, 3, 3, '2026-06-15 20:00:00'],  // Kulai Rangers vs Pontian FC
        ];

        $matchGames = [];

        // Create completed matches
        foreach ($completedMatches as $idx => $m) {
            $matchGames[] = MatchGame::create([
                'competition_id'      => $superLeague->id,
                'home_team_id'        => $superLeagueTeams[$m[0]]->id,
                'away_team_id'        => $superLeagueTeams[$m[1]]->id,
                'home_score'          => $m[2],
                'away_score'          => $m[3],
                'matchday'            => $m[4],
                'match_date'          => $m[6],
                'venue'               => $venues[$m[5]],
                'status'              => 'completed',
                'referee'             => $referees[$idx % count($referees)],
                'assistant_referee_1' => 'AR1 ' . ($idx + 1),
                'assistant_referee_2' => 'AR2 ' . ($idx + 1),
            ]);
        }

        // Create scheduled matches
        foreach ($scheduledMatches as $idx => $m) {
            $matchGames[] = MatchGame::create([
                'competition_id' => $superLeague->id,
                'home_team_id'   => $superLeagueTeams[$m[0]]->id,
                'away_team_id'   => $superLeagueTeams[$m[1]]->id,
                'home_score'     => 0,
                'away_score'     => 0,
                'matchday'       => $m[4],
                'match_date'     => $m[6],
                'venue'          => $venues[$m[5]],
                'status'         => 'scheduled',
                'referee'        => $referees[($idx + 2) % count($referees)],
            ]);
        }

        // ──────────────────────────────────────────────
        // 9. Match Lineups & Events (completed matches)
        // ──────────────────────────────────────────────
        foreach ($completedMatches as $mIdx => $mData) {
            $match = $matchGames[$mIdx];
            $homeTeamIdx = $mData[0];
            $awayTeamIdx = $mData[1];
            $homeScore = $mData[2];
            $awayScore = $mData[3];

            $homePlayers = $allPlayers[$homeTeamIdx];
            $awayPlayers = $allPlayers[$awayTeamIdx];

            // Create lineups: 11 starters + 4 subs per team
            foreach ([$homePlayers, $awayPlayers] as $side => $playerList) {
                $teamId = $side === 0 ? $superLeagueTeams[$homeTeamIdx]->id : $superLeagueTeams[$awayTeamIdx]->id;
                foreach ($playerList as $pIdx => $player) {
                    MatchLineup::create([
                        'match_game_id' => $match->id,
                        'team_id'       => $teamId,
                        'player_id'     => $player->id,
                        'jersey_number' => $player->jersey_number,
                        'position'      => $player->position,
                        'is_starting'   => $pIdx < 11,
                    ]);
                }
            }

            // Goals for home team
            $homeGoalMinutes = $this->generateMinutes($homeScore);
            foreach ($homeGoalMinutes as $minute) {
                $scorerIdx = rand(6, 10);
                MatchEvent::create([
                    'match_game_id' => $match->id,
                    'team_id'       => $superLeagueTeams[$homeTeamIdx]->id,
                    'player_id'     => $homePlayers[$scorerIdx]->id,
                    'event_type'    => 'goal',
                    'minute'        => $minute,
                ]);
            }

            // Goals for away team
            $awayGoalMinutes = $this->generateMinutes($awayScore);
            foreach ($awayGoalMinutes as $minute) {
                $scorerIdx = rand(6, 10);
                MatchEvent::create([
                    'match_game_id' => $match->id,
                    'team_id'       => $superLeagueTeams[$awayTeamIdx]->id,
                    'player_id'     => $awayPlayers[$scorerIdx]->id,
                    'event_type'    => 'goal',
                    'minute'        => $minute,
                ]);
            }

            // Yellow cards (2-4 per match)
            $yellowCount = rand(2, 4);
            for ($y = 0; $y < $yellowCount; $y++) {
                $isHome = (bool) rand(0, 1);
                $teamIdx = $isHome ? $homeTeamIdx : $awayTeamIdx;
                $players = $isHome ? $homePlayers : $awayPlayers;
                $cardPlayerIdx = rand(2, 10);
                MatchEvent::create([
                    'match_game_id' => $match->id,
                    'team_id'       => $superLeagueTeams[$teamIdx]->id,
                    'player_id'     => $players[$cardPlayerIdx]->id,
                    'event_type'    => 'yellow_card',
                    'minute'        => rand(15, 88),
                ]);
            }

            // Substitutions (one per team)
            // Home team substitution
            $subOutIdx = rand(6, 10);
            $subInIdx  = rand(11, 14);
            $subMinute = rand(55, 75);
            MatchEvent::create([
                'match_game_id'     => $match->id,
                'team_id'           => $superLeagueTeams[$homeTeamIdx]->id,
                'player_id'         => $homePlayers[$subOutIdx]->id,
                'event_type'        => 'substitution_out',
                'minute'            => $subMinute,
                'related_player_id' => $homePlayers[$subInIdx]->id,
            ]);
            MatchEvent::create([
                'match_game_id'     => $match->id,
                'team_id'           => $superLeagueTeams[$homeTeamIdx]->id,
                'player_id'         => $homePlayers[$subInIdx]->id,
                'event_type'        => 'substitution_in',
                'minute'            => $subMinute,
                'related_player_id' => $homePlayers[$subOutIdx]->id,
            ]);

            // Away team substitution
            $subOutIdx = rand(6, 10);
            $subInIdx  = rand(11, 14);
            $subMinute = rand(60, 80);
            MatchEvent::create([
                'match_game_id'     => $match->id,
                'team_id'           => $superLeagueTeams[$awayTeamIdx]->id,
                'player_id'         => $awayPlayers[$subOutIdx]->id,
                'event_type'        => 'substitution_out',
                'minute'            => $subMinute,
                'related_player_id' => $awayPlayers[$subInIdx]->id,
            ]);
            MatchEvent::create([
                'match_game_id'     => $match->id,
                'team_id'           => $superLeagueTeams[$awayTeamIdx]->id,
                'player_id'         => $awayPlayers[$subInIdx]->id,
                'event_type'        => 'substitution_in',
                'minute'            => $subMinute,
                'related_player_id' => $awayPlayers[$subOutIdx]->id,
            ]);
        }

        // ──────────────────────────────────────────────
        // 10. Standings (Super League, calculated from matches)
        // ──────────────────────────────────────────────
        $standingsData = [];
        for ($i = 0; $i < 10; $i++) {
            $standingsData[$i] = [
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'goals_for' => 0, 'goals_against' => 0,
            ];
        }

        // Calculate from completed matches
        // Match 1: Team 0 (1) vs Team 1 (0) => Team 0 wins
        // Match 2: Team 2 (2) vs Team 3 (1) => Team 2 wins
        // Match 3: Team 4 (0) vs Team 5 (0) => Draw
        // Match 4: Team 6 (3) vs Team 7 (2) => Team 6 wins
        // Match 5: Team 8 (1) vs Team 9 (1) => Draw
        foreach ($completedMatches as $m) {
            $h = $m[0];
            $a = $m[1];
            $hg = $m[2];
            $ag = $m[3];

            $standingsData[$h]['played']++;
            $standingsData[$a]['played']++;
            $standingsData[$h]['goals_for'] += $hg;
            $standingsData[$h]['goals_against'] += $ag;
            $standingsData[$a]['goals_for'] += $ag;
            $standingsData[$a]['goals_against'] += $hg;

            if ($hg > $ag) {
                $standingsData[$h]['won']++;
                $standingsData[$a]['lost']++;
            } elseif ($hg < $ag) {
                $standingsData[$a]['won']++;
                $standingsData[$h]['lost']++;
            } else {
                $standingsData[$h]['drawn']++;
                $standingsData[$a]['drawn']++;
            }
        }

        // Calculate points and goal difference, then sort
        $standingsList = [];
        foreach ($standingsData as $i => $s) {
            $points = ($s['won'] * 3) + ($s['drawn'] * 1);
            $gd = $s['goals_for'] - $s['goals_against'];
            $standingsList[] = array_merge($s, [
                'team_index'      => $i,
                'goal_difference' => $gd,
                'points'          => $points,
            ]);
        }

        usort($standingsList, function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
            if ($a['goal_difference'] !== $b['goal_difference']) return $b['goal_difference'] - $a['goal_difference'];
            return $b['goals_for'] - $a['goals_for'];
        });

        foreach ($standingsList as $pos => $s) {
            Standing::create([
                'competition_id'  => $superLeague->id,
                'team_id'         => $superLeagueTeams[$s['team_index']]->id,
                'group_id'        => null,
                'played'          => $s['played'],
                'won'             => $s['won'],
                'drawn'           => $s['drawn'],
                'lost'            => $s['lost'],
                'goals_for'       => $s['goals_for'],
                'goals_against'   => $s['goals_against'],
                'goal_difference' => $s['goal_difference'],
                'points'          => $s['points'],
                'position'        => $pos + 1,
            ]);
        }
    }

    /**
     * Generate sorted random minutes for goal events.
     */
    private function generateMinutes(int $count): array
    {
        $minutes = [];
        for ($i = 0; $i < $count; $i++) {
            $minutes[] = rand(5, 89);
        }
        sort($minutes);
        return $minutes;
    }
}
