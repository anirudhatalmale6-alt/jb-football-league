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
        // 1. Users
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

        // (a) SUMBANGSIH CUP
        $sumbangsihCup = Competition::create([
            'name'        => 'SUMBANGSIH CUP',
            'season'      => '2026',
            'type'        => 'knockout',
            'status'      => 'upcoming',
            'start_date'  => '2026-01-01',
            'end_date'    => '2026-01-15',
            'description' => 'Pre-season knockout cup between the top two clubs.',
        ]);

        // (b) JBFA SUPER LEAGUE
        $superLeague = Competition::create([
            'name'          => 'JBFA SUPER LEAGUE',
            'season'        => '2026',
            'type'          => 'league',
            'status'        => 'active',
            'start_date'    => '2026-01-15',
            'end_date'      => '2026-06-30',
            'description'   => 'Top-tier league of the Johor Bahru Football Association.',
            'max_players'   => 25,
            'max_officials' => 7,
        ]);

        // (c) JBFA PREMIER LEAGUE
        $premierLeague = Competition::create([
            'name'          => 'JBFA PREMIER LEAGUE',
            'season'        => '2026',
            'type'          => 'league',
            'status'        => 'active',
            'start_date'    => '2026-02-01',
            'end_date'      => '2026-07-15',
            'description'   => 'Second-tier league divided into two groups.',
            'max_players'   => 25,
            'max_officials' => 7,
        ]);

        // (d) JBFA DIVISION LEAGUE
        $divisionLeague = Competition::create([
            'name'          => 'JBFA DIVISION LEAGUE',
            'season'        => '2026',
            'type'          => 'league',
            'status'        => 'active',
            'start_date'    => '2026-03-01',
            'end_date'      => '2026-08-30',
            'description'   => 'Grassroots divisional league with six regional groups.',
            'max_players'   => 25,
            'max_officials' => 7,
        ]);

        // (e) JBFA FA CUP
        $faCup = Competition::create([
            'name'        => 'JBFA FA CUP',
            'season'      => '2026',
            'type'        => 'knockout',
            'status'      => 'upcoming',
            'start_date'  => '2026-04-01',
            'end_date'    => '2026-06-30',
            'description' => 'Knockout cup competition open to top clubs across divisions.',
        ]);

        // ──────────────────────────────────────────────
        // 3. Groups
        // ──────────────────────────────────────────────

        // Premier League — 2 groups
        $premierGroupA = Group::create(['competition_id' => $premierLeague->id, 'name' => 'Kumpulan A', 'order' => 1]);
        $premierGroupB = Group::create(['competition_id' => $premierLeague->id, 'name' => 'Kumpulan B', 'order' => 2]);

        // Division League — 6 groups
        $divisionGroups = [];
        for ($g = 1; $g <= 6; $g++) {
            $divisionGroups[] = Group::create([
                'competition_id' => $divisionLeague->id,
                'name'           => 'Kumpulan ' . $g,
                'order'          => $g,
            ]);
        }

        // ──────────────────────────────────────────────
        // 4. Teams
        // ──────────────────────────────────────────────

        // Helper to generate email from team name
        $makeEmail = function (string $name): string {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $name));
            return 'info@' . $slug . '.com';
        };

        // Helper to generate short name from team name
        $makeShortName = function (string $name): string {
            // Remove " FC" suffix for abbreviation
            $clean = preg_replace('/\s+FC$/i', '', $name);
            $words = preg_split('/\s+/', $clean);
            if (count($words) === 1) {
                return strtoupper(substr($clean, 0, 3));
            }
            $abbr = '';
            foreach ($words as $w) {
                $abbr .= strtoupper(substr($w, 0, 1));
            }
            // Append FC if original had it
            if (preg_match('/FC$/i', $name)) {
                $abbr .= ' FC';
            }
            return $abbr;
        };

        // Malaysian manager names pool
        $managerNames = [
            'Mohd Tarmizi Ali', 'Kamal Bahari', 'Zainol Abidin', 'Ismail Sabri', 'Roslan Mat Daud',
            'Hasbullah Awang', 'Mohd Nor Ibrahim', 'Ahmad Tajuddin', 'Zainal Abidin Mat', 'Mohd Razali Hassan',
            'Lee Hock Seng', 'Tan Boon Keat', 'Ong Chin Huat', 'Wong Siu Lun', 'Lim Teik Bee',
            'Yap Chee Seng', 'Foo Wai Meng', 'Goh Choon Keat', 'Lim Boon Chong', 'Chan Wai Kit',
            'R. Gunasegaran', 'K. Thanaraj', 'S. Kuganesh', 'Muthu Kannan', 'Bala Subramaniam',
            'Ahmad Fadzil Hamid', 'Mohd Azlan Yusof', 'Mohd Safwan Ali', 'Ahmad Nazmi Kadir', 'Mohd Ridzuan Nordin',
            'Mohd Aizat Wahab', 'Ahmad Luqman Hakim', 'Mohd Zikri Azman', 'Mohd Afiq Roslan', 'Ahmad Kamal Noor',
            'Ng Jia Hao', 'Koh Boon Huat', 'Teo Wei Lun', 'Cheah Hong Leong', 'Sim Wei Chong',
            'P. Navin Kumar', 'V. Sutharsan', 'L. Kogulraj', 'N. Harindran', 'T. Saravanan',
            'Mohd Khairul Anwar', 'Goh Yong Seng', 'Lau Kah Fai', 'Ahmad Fitri Hussin', 'Mohd Farhan Idris',
            'Mohd Izzuddin Shah', 'Yap Jian Ming', 'Foo Kok Wai', 'Mohd Shahril Ishak', 'Ahmad Fadhil Hamdan',
            'Mohd Ikmal Harun', 'A. Vikneswaran', 'R. Praveen Kumar', 'S. Mahendran', 'K. Ravichandran',
            'Ahmad Hazim Latif', 'Mohd Aiman Zakaria', 'Mohd Taufiq Hidayat', 'Ahmad Syahmi Redzuan', 'Mohd Adib Azfar',
            'Chong Kok Wah', 'Tan Chee Hong', 'Lee Zhi Wei', 'Tan Jun Hao', 'Lim Kai Xiang',
        ];
        $managerIndex = 0;

        $getManager = function () use (&$managerNames, &$managerIndex): string {
            $name = $managerNames[$managerIndex % count($managerNames)];
            $managerIndex++;
            return $name;
        };

        // --- (a) SUMBANGSIH CUP (2 teams) ---
        $sumbangsihTeamNames = ['MBIP FC', 'JMF FC'];
        foreach ($sumbangsihTeamNames as $tName) {
            Team::create([
                'competition_id' => $sumbangsihCup->id,
                'group_id'       => null,
                'name'           => $tName,
                'short_name'     => $makeShortName($tName),
                'manager_name'   => $getManager(),
                'contact_email'  => $makeEmail($tName),
                'status'         => 'approved',
            ]);
        }

        // --- (b) JBFA SUPER LEAGUE (10 teams, no groups) ---
        $superLeagueTeamNames = [
            'MBIP FC', 'JMF FC', 'PTP FC', 'MBPG FC', 'MASAI UNITED FC',
            'ALOYA FC', 'SG TIRAM FC', 'MBJB FC', 'HUMA WARRIOR FC', 'KANGKAR PULAI FC',
        ];

        $superLeagueTeams = [];
        foreach ($superLeagueTeamNames as $tName) {
            $superLeagueTeams[] = Team::create([
                'competition_id' => $superLeague->id,
                'group_id'       => null,
                'name'           => $tName,
                'short_name'     => $makeShortName($tName),
                'manager_name'   => $getManager(),
                'contact_email'  => $makeEmail($tName),
                'status'         => 'approved',
            ]);
        }

        // --- (c) JBFA PREMIER LEAGUE (12 teams, 2 groups of 6) ---
        $premierTeamNames = [
            // Group A (first 6)
            'SAUJANA FC', 'RMPJB FC', 'JMF JUNIOR FC', 'BUKIT MUTIARA FC', 'MELOR FC', 'IPGKTI FC',
            // Group B (last 6)
            'AMPM FC', 'IMIGRESEN JOHOR FC', 'GELANG PATAH FC', 'NEWSTAR FC', 'MALABAR FC', 'PG CITY FC',
        ];

        foreach ($premierTeamNames as $idx => $tName) {
            $groupId = $idx < 6 ? $premierGroupA->id : $premierGroupB->id;
            Team::create([
                'competition_id' => $premierLeague->id,
                'group_id'       => $groupId,
                'name'           => $tName,
                'short_name'     => $makeShortName($tName),
                'manager_name'   => $getManager(),
                'contact_email'  => $makeEmail($tName),
                'status'         => 'approved',
            ]);
        }

        // --- (d) JBFA DIVISION LEAGUE (30 teams, 6 groups of 5) ---
        $divisionTeamNames = [
            // Kumpulan 1
            'ULU TEBRAU UNITED FC', 'PSJ FC', 'MY JB CITY FC', 'ONE DREAM FC', 'JBC FC',
            // Kumpulan 2
            'VOCKET FC', 'BORRNEO FC', 'SEDAGHER FC', 'LARKIN RANGERS FC', 'SKUDAI TIGERS FC',
            // Kumpulan 3
            'BEMBAN JUNIOR FC', 'COLONY FC', 'PENDAS BARU FC', 'COUSIN UNITED FC', 'PUTERA TIMUR FC',
            // Kumpulan 4
            'BBU FC', 'KASTAM FC', 'PINE FC', 'TEBU HITAM FC', 'TEMPORARY FC',
            // Kumpulan 5
            'PERLING LEGENDS FC', 'BAYU LEGEND FC', 'VACASIA FC', 'MAJIDEE BARU FC', 'TDA FC',
            // Kumpulan 6
            'RINTING GENERATION FC', 'FC BTEAM', 'PG ROVERS FC', 'NEW TEAM', 'NEW TEAM 2',
        ];

        foreach ($divisionTeamNames as $idx => $tName) {
            $groupIndex = intdiv($idx, 5);
            Team::create([
                'competition_id' => $divisionLeague->id,
                'group_id'       => $divisionGroups[$groupIndex]->id,
                'name'           => $tName,
                'short_name'     => $makeShortName($tName),
                'manager_name'   => $getManager(),
                'contact_email'  => $makeEmail($tName),
                'status'         => 'approved',
            ]);
        }

        // --- (e) JBFA FA CUP (16 teams, no groups) ---
        $faCupTeamNames = [
            'MBIP FC', 'JMF FC', 'PTP FC', 'MBPG FC', 'MASAI UNITED FC', 'ALOYA FC',
            'SG TIRAM FC', 'MBJB FC', 'HUMA WARRIOR FC', 'KANGKAR PULAI FC',
            'SAUJANA FC', 'RMPJB FC', 'JMF JUNIOR FC', 'BUKIT MUTIARA FC',
            'ONE DREAM FC', 'PSJ FC',
        ];

        foreach ($faCupTeamNames as $tName) {
            Team::create([
                'competition_id' => $faCup->id,
                'group_id'       => null,
                'name'           => $tName,
                'short_name'     => $makeShortName($tName),
                'manager_name'   => $getManager(),
                'contact_email'  => $makeEmail($tName),
                'status'         => 'approved',
            ]);
        }

        // ──────────────────────────────────────────────
        // 5. Assign team managers
        // ──────────────────────────────────────────────
        $teamManager1->update(['team_id' => $superLeagueTeams[0]->id]); // Ahmad Razak -> MBIP FC
        $teamManager2->update(['team_id' => $superLeagueTeams[1]->id]); // Lim Wei Keat -> JMF FC

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
        // 7. Officials (3 per Super League team = 30 total)
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
            'Kompleks Sukan Majlis Bandaraya JB',
            'Stadium Larkin',
            'Stadium Tan Sri Dato Haji Hassan Yunos',
        ];

        $referees = [
            'Nazmi Nasaruddin',
            'Ahmad Yazid Yasin',
            'Suresh Ramalingam',
            'Mohd Amirul Izwan',
            'Sivakumar Kandasamy',
        ];

        // 5 completed matches: [homeIdx, awayIdx, homeScore, awayScore, matchday, venueIdx, datetime]
        $completedMatches = [
            [0, 1, 1, 0, 1, 0, '2026-02-01 20:00:00'],  // MBIP FC 1-0 JMF FC
            [2, 3, 2, 1, 1, 1, '2026-02-01 16:00:00'],  // PTP FC 2-1 MBPG FC
            [4, 5, 0, 0, 1, 2, '2026-02-02 20:00:00'],  // MASAI UNITED FC 0-0 ALOYA FC
            [6, 7, 3, 2, 2, 3, '2026-02-15 20:00:00'],  // SG TIRAM FC 3-2 MBJB FC
            [8, 9, 1, 1, 2, 4, '2026-02-15 16:00:00'],  // HUMA WARRIOR FC 1-1 KANGKAR PULAI FC
        ];

        // 3 scheduled matches
        $scheduledMatches = [
            [0, 2, 0, 0, 3, 0, '2026-06-14 20:00:00'],  // MBIP FC vs PTP FC
            [1, 4, 0, 0, 3, 1, '2026-06-14 16:00:00'],  // JMF FC vs MASAI UNITED FC
            [3, 5, 0, 0, 3, 2, '2026-06-15 20:00:00'],  // MBPG FC vs ALOYA FC
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
                $scorerIdx = rand(6, 10); // midfielders/forwards
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
