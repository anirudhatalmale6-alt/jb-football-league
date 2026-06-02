<?php

namespace Database\Seeders;

use App\Models\Competition;
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
        // 1. Competition
        // ──────────────────────────────────────────────
        $competition = Competition::create([
            'name'        => 'JB Premier League 2026',
            'season'      => '2025/2026',
            'type'        => 'league',
            'status'      => 'active',
            'start_date'  => '2026-01-15',
            'end_date'    => '2026-06-30',
            'description' => 'The premier football league competition in Johor Bahru featuring the best local clubs.',
        ]);

        // ──────────────────────────────────────────────
        // 2. Teams
        // ──────────────────────────────────────────────
        $teamData = [
            ['name' => 'Johor United FC',      'short_name' => 'JU FC',      'manager_name' => 'Ahmad Razak',        'contact_email' => 'info@johorunited.com',      'contact_phone' => '07-2345678'],
            ['name' => 'Pasir Gudang Rangers', 'short_name' => 'PG Rangers', 'manager_name' => 'Lim Wei Keat',       'contact_email' => 'info@pgrangers.com',        'contact_phone' => '07-2518899'],
            ['name' => 'Skudai City FC',       'short_name' => 'Skudai FC',  'manager_name' => 'Mohd Faizal Ismail', 'contact_email' => 'admin@skudaicityfc.com',    'contact_phone' => '07-5563344'],
            ['name' => 'Senai Strikers',       'short_name' => 'Senai SK',   'manager_name' => 'Tan Chee Hong',      'contact_email' => 'contact@senaistrikers.com', 'contact_phone' => '07-5992211'],
            ['name' => 'Kulai Warriors',       'short_name' => 'Kulai WR',   'manager_name' => 'Syed Amir Hamzah',   'contact_email' => 'info@kulaiwarriors.com',    'contact_phone' => '07-6631122'],
            ['name' => 'Iskandar Puteri FC',   'short_name' => 'IP FC',      'manager_name' => 'R. Gunasegaran',     'contact_email' => 'office@ipfc.com',           'contact_phone' => '07-5101188'],
        ];

        $teams = [];
        foreach ($teamData as $data) {
            $teams[] = Team::create(array_merge($data, [
                'competition_id' => $competition->id,
                'status'         => 'approved',
            ]));
        }

        // ──────────────────────────────────────────────
        // 3. Users
        // ──────────────────────────────────────────────
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@jbfl.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
            'team_id'  => null,
        ]);

        User::create([
            'name'     => 'League Admin',
            'email'    => 'league@jbfl.com',
            'password' => Hash::make('password'),
            'role'     => 'league_admin',
            'team_id'  => null,
        ]);

        User::create([
            'name'     => 'Ahmad Razak',
            'email'    => 'ahmad@team1.com',
            'password' => Hash::make('password'),
            'role'     => 'team_manager',
            'team_id'  => $teams[0]->id,
        ]);

        User::create([
            'name'     => 'Lim Wei Keat',
            'email'    => 'lim@team2.com',
            'password' => Hash::make('password'),
            'role'     => 'team_manager',
            'team_id'  => $teams[1]->id,
        ]);

        // ──────────────────────────────────────────────
        // 4. Players (15 per team, 90 total)
        // ──────────────────────────────────────────────
        // Malaysian-sounding names pool (multi-ethnic: Malay, Chinese, Indian, Sabah/Sarawak)
        $namePool = [
            // Malay names
            'Muhammad Aiman', 'Muhammad Haziq', 'Ahmad Firdaus', 'Mohd Syazwan', 'Mohd Hafiz',
            'Muhammad Arif', 'Ahmad Zulkifli', 'Mohd Farid', 'Muhammad Irfan', 'Ahmad Danial',
            'Mohd Azlan', 'Muhammad Nabil', 'Ahmad Shahrul', 'Mohd Izzuddin', 'Muhammad Amirul',
            'Ahmad Fadzil', 'Mohd Ridzuan', 'Muhammad Hakim', 'Ahmad Luqman', 'Mohd Khairul',
            'Muhammad Aidil', 'Ahmad Nazmi', 'Mohd Azri', 'Muhammad Farhan', 'Ahmad Kamal',
            'Mohd Safwan', 'Muhammad Zikri', 'Ahmad Fitri', 'Mohd Aizat', 'Muhammad Afiq',
            // Chinese names
            'Lee Zhi Wei', 'Tan Jun Hao', 'Lim Kai Xiang', 'Ong Wei Ming', 'Ng Jia Hao',
            'Wong Chee Keong', 'Chan Wai Kit', 'Goh Yong Seng', 'Lau Kah Fai', 'Koh Boon Huat',
            'Teo Wei Lun', 'Yap Jian Ming', 'Cheah Hong Leong', 'Foo Kok Wai', 'Sim Wei Chong',
            // Indian names
            'K. Thanaraj', 'S. Kuganesh', 'R. Praveen', 'M. Dinesh Kumar', 'T. Saravanan',
            'A. Vikneswaran', 'P. Navin Kumar', 'V. Sutharsan', 'L. Kogulraj', 'N. Harindran',
            // East Malaysian names
            'Maximus Matin', 'Ronny Harun', 'Rickie Bujang', 'Saddil Ramdani', 'Bobby Gonzaga',
            'Jerry Mawieh', 'Mohamadou Sumareh', 'Kenny Pallraj', 'Shahrel Fikri', 'Akhyar Rashid',
            'Syafiq Ahmad', 'Dion Cools', 'Junior Eldstal', 'Natxo Insa', 'Safawi Rasid',
            'Afiq Fazail', 'Syamer Kutty', 'Corbin Ong', 'Quentin Cheng', 'Dominic Tan',
            'Matthew Davies', 'Gary Steven', 'Baddrol Bakhtiar', 'Norshahrul Idlan', 'Farizal Marlias',
            'Khairulazhan Khalid', 'Hazwan Bakri', 'Shahrul Saad', 'Syahmi Safari', 'Aidil Zafuan',
            // Additional names
            'Azammuddin Akil', 'Irfan Zakaria', 'Wan Zack Haikal', 'Luqman Hakim', 'Nik Akif Syahiran',
        ];

        // Shuffle names so each team gets a unique set
        shuffle($namePool);

        // Position template per team: 2 GK, 4 DEF, 5 MID, 4 FWD
        $positionTemplate = [
            'goalkeeper', 'goalkeeper',
            'defender', 'defender', 'defender', 'defender',
            'midfielder', 'midfielder', 'midfielder', 'midfielder', 'midfielder',
            'forward', 'forward', 'forward', 'forward',
        ];

        $allPlayers = []; // $allPlayers[teamIndex] = [Player, ...]
        $nameIndex = 0;

        foreach ($teams as $ti => $team) {
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
        // 5. Officials (3 per team)
        // ──────────────────────────────────────────────
        $officialNames = [
            ['Mohd Tarmizi Ali',      'Lee Hock Seng',        'Saraswathi Devi'],
            ['Kamal Bahari',          'Chong Kok Wah',        'Priya Nair'],
            ['Zainol Abidin',         'Tan Boon Keat',        'Rajesh Menon'],
            ['Ismail Sabri',          'Ong Chin Huat',        'Muthu Kannan'],
            ['Roslan Mat Daud',       'Wong Siu Lun',         'Bala Subramaniam'],
            ['Hasbullah Awang',       'Lim Teik Bee',         'Anand Krishnan'],
        ];
        $officialRoles = ['Head Coach', 'Assistant Coach', 'Team Physio'];

        foreach ($teams as $ti => $team) {
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
        // 6. Match Games (10 total)
        // ──────────────────────────────────────────────
        $venues = [
            'Sultan Ibrahim Stadium',
            'Pasir Gudang Stadium',
            'Skudai Sports Complex',
            'Senai Recreation Ground',
            'Kulai Community Stadium',
            'Iskandar Puteri Arena',
        ];

        $referees = [
            'Nazmi Nasaruddin',
            'Ahmad Yazid Yasin',
            'Suresh Ramalingam',
            'Mohd Amirul Izwan',
            'Sivakumar Kandasamy',
        ];

        // Completed matches (matchday 1-3)
        // Match results: [home_team_idx, away_team_idx, home_score, away_score, matchday, venue_idx, date]
        $completedMatches = [
            [0, 1, 1, 0, 1, 0, '2026-02-01 20:00:00'],  // Johor United 1-0 PG Rangers
            [2, 3, 2, 1, 1, 2, '2026-02-01 16:00:00'],  // Skudai City 2-1 Senai Strikers
            [4, 5, 0, 0, 1, 4, '2026-02-02 20:00:00'],  // Kulai Warriors 0-0 Iskandar Puteri
            [1, 2, 3, 2, 2, 1, '2026-02-15 20:00:00'],  // PG Rangers 3-2 Skudai City
            [3, 0, 1, 1, 2, 3, '2026-02-15 16:00:00'],  // Senai Strikers 1-1 Johor United
        ];

        // Scheduled future matches
        $scheduledMatches = [
            [5, 0, 0, 0, 3, 5, '2026-06-14 20:00:00'],  // IP FC vs Johor United
            [1, 4, 0, 0, 3, 1, '2026-06-14 16:00:00'],  // PG Rangers vs Kulai Warriors
            [3, 5, 0, 0, 3, 3, '2026-06-15 20:00:00'],  // Senai Strikers vs IP FC
        ];

        // Additional matchday 4 scheduled matches
        $scheduledMatchday4 = [
            [0, 4, 0, 0, 4, 0, '2026-06-21 20:00:00'],  // Johor United vs Kulai Warriors
            [2, 1, 0, 0, 4, 2, '2026-06-21 16:00:00'],  // Skudai City vs PG Rangers
        ];

        $matchGames = [];

        // Create completed matches
        foreach ($completedMatches as $idx => $m) {
            $matchGames[] = MatchGame::create([
                'competition_id' => $competition->id,
                'home_team_id'   => $teams[$m[0]]->id,
                'away_team_id'   => $teams[$m[1]]->id,
                'home_score'     => $m[2],
                'away_score'     => $m[3],
                'matchday'       => $m[4],
                'match_date'     => $m[6],
                'venue'          => $venues[$m[5]],
                'status'         => 'completed',
                'referee'        => $referees[$idx % count($referees)],
                'assistant_referee_1' => 'AR1 ' . ($idx + 1),
                'assistant_referee_2' => 'AR2 ' . ($idx + 1),
            ]);
        }

        // Create scheduled matches (matchday 3)
        foreach ($scheduledMatches as $idx => $m) {
            $matchGames[] = MatchGame::create([
                'competition_id' => $competition->id,
                'home_team_id'   => $teams[$m[0]]->id,
                'away_team_id'   => $teams[$m[1]]->id,
                'home_score'     => 0,
                'away_score'     => 0,
                'matchday'       => $m[4],
                'match_date'     => $m[6],
                'venue'          => $venues[$m[5]],
                'status'         => 'scheduled',
                'referee'        => $referees[($idx + 2) % count($referees)],
            ]);
        }

        // Create scheduled matches (matchday 4 - different matchday)
        foreach ($scheduledMatchday4 as $idx => $m) {
            $matchGames[] = MatchGame::create([
                'competition_id' => $competition->id,
                'home_team_id'   => $teams[$m[0]]->id,
                'away_team_id'   => $teams[$m[1]]->id,
                'home_score'     => 0,
                'away_score'     => 0,
                'matchday'       => $m[4],
                'match_date'     => $m[6],
                'venue'          => $venues[$m[5]],
                'status'         => 'scheduled',
                'referee'        => $referees[($idx + 3) % count($referees)],
            ]);
        }

        // ──────────────────────────────────────────────
        // 7. Match Lineups & Events (for completed matches only)
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
                $teamId = $side === 0 ? $teams[$homeTeamIdx]->id : $teams[$awayTeamIdx]->id;
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

            // Create match events
            // Goals for home team
            $homeGoalMinutes = $this->generateMinutes($homeScore);
            foreach ($homeGoalMinutes as $minute) {
                // Pick a forward or midfielder from the starters (indices 6-10 are MID/FWD)
                $scorerIdx = rand(6, 10);
                MatchEvent::create([
                    'match_game_id' => $match->id,
                    'team_id'       => $teams[$homeTeamIdx]->id,
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
                    'team_id'       => $teams[$awayTeamIdx]->id,
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
                $cardPlayerIdx = rand(2, 10); // defenders to forwards
                MatchEvent::create([
                    'match_game_id' => $match->id,
                    'team_id'       => $teams[$teamIdx]->id,
                    'player_id'     => $players[$cardPlayerIdx]->id,
                    'event_type'    => 'yellow_card',
                    'minute'        => rand(15, 88),
                ]);
            }

            // Substitutions (2 per match - one per team)
            // Home team substitution
            $subOutIdx = rand(6, 10); // a starter
            $subInIdx  = rand(11, 14); // a sub
            MatchEvent::create([
                'match_game_id'    => $match->id,
                'team_id'          => $teams[$homeTeamIdx]->id,
                'player_id'        => $homePlayers[$subOutIdx]->id,
                'event_type'       => 'substitution_out',
                'minute'           => rand(55, 75),
                'related_player_id' => $homePlayers[$subInIdx]->id,
            ]);
            MatchEvent::create([
                'match_game_id'    => $match->id,
                'team_id'          => $teams[$homeTeamIdx]->id,
                'player_id'        => $homePlayers[$subInIdx]->id,
                'event_type'       => 'substitution_in',
                'minute'           => rand(55, 75),
                'related_player_id' => $homePlayers[$subOutIdx]->id,
            ]);

            // Away team substitution
            $subOutIdx = rand(6, 10);
            $subInIdx  = rand(11, 14);
            MatchEvent::create([
                'match_game_id'    => $match->id,
                'team_id'          => $teams[$awayTeamIdx]->id,
                'player_id'        => $awayPlayers[$subOutIdx]->id,
                'event_type'       => 'substitution_out',
                'minute'           => rand(60, 80),
                'related_player_id' => $awayPlayers[$subInIdx]->id,
            ]);
            MatchEvent::create([
                'match_game_id'    => $match->id,
                'team_id'          => $teams[$awayTeamIdx]->id,
                'player_id'        => $awayPlayers[$subInIdx]->id,
                'event_type'       => 'substitution_in',
                'minute'           => rand(60, 80),
                'related_player_id' => $awayPlayers[$subOutIdx]->id,
            ]);
        }

        // ──────────────────────────────────────────────
        // 8. Standings (calculated from completed matches)
        // ──────────────────────────────────────────────
        // Initialize standings data for all 6 teams
        $standingsData = [];
        for ($i = 0; $i < 6; $i++) {
            $standingsData[$i] = [
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'goals_for' => 0, 'goals_against' => 0,
            ];
        }

        // Calculate from completed matches
        // Match 1: Team 0 (1) vs Team 1 (0) => Team 0 wins
        // Match 2: Team 2 (2) vs Team 3 (1) => Team 2 wins
        // Match 3: Team 4 (0) vs Team 5 (0) => Draw
        // Match 4: Team 1 (3) vs Team 2 (2) => Team 1 wins
        // Match 5: Team 3 (1) vs Team 0 (1) => Draw
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

        // Sort by points desc, then goal difference desc, then goals_for desc
        usort($standingsList, function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
            if ($a['goal_difference'] !== $b['goal_difference']) return $b['goal_difference'] - $a['goal_difference'];
            return $b['goals_for'] - $a['goals_for'];
        });

        foreach ($standingsList as $pos => $s) {
            Standing::create([
                'competition_id'  => $competition->id,
                'team_id'         => $teams[$s['team_index']]->id,
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
