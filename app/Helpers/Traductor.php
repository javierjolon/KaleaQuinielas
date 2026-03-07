<?php


    function traducir_equipos(string $teamName): string
    {
        $translations = [
            'Germany' => 'Alemania',
            'Spain' => 'España',
            'France' => 'Francia',
            'England' => 'Inglaterra',
            'Brazil' => 'Brasil',
            'Argentina' => 'Argentina',
            'Mexico' => 'México',
            'United States' => 'Estados Unidos',
            'Netherlands' => 'Países Bajos',
            'Portugal' => 'Portugal',
            'Italy' => 'Italia',
            'Belgium' => 'Bélgica',
            'Croatia' => 'Croacia',
            'Uruguay' => 'Uruguay',
            'Colombia' => 'Colombia',
            'Japan' => 'Japón',
            'South Korea' => 'Corea del Sur',
            'Saudi Arabia' => 'Arabia Saudita',
            'Australia' => 'Australia',
            'Switzerland' => 'Suiza',
            'Denmark' => 'Dinamarca',
            'Poland' => 'Polonia',
            'Serbia' => 'Serbia',
            'Morocco' => 'Marruecos',
            'Cameroon' => 'Camerún',
            'Senegal' => 'Senegal',
            'Canada' => 'Canadá',
            'Ecuador' => 'Ecuador',
            'Qatar' => 'Catar',
            "Ivory Coast" => 'Costa de Marfil',
            'Cape Verde Islands' => 'Cabo Verde',
            'South Africa' => 'Sudáfrica',
            'Curaçao' => 'Curazao',
            'Scotland' => 'Escocia',
            'Egypt' => 'Egipto',
            'Tunisia' => 'Túnez',
            'New Zealand' => 'Nueva Zelanda',
            'Norway' => 'Noruega',
            'Jordan' => 'Jordania',
            'Algeria' => 'Argelia',
        ];

        return $translations[$teamName] ?? $teamName;
    }

    function traducir_status(string $status){
        $translations = [
            "TIMED" => "Programado",
            "IN_PLAY" => "En juego",
            "LIVE" => "En juego",
            "PAUSED" => "Medio tiempo",
            "FINISHED" => "Finalizdo",
            "SUSPENDED" => "Suspendido",
            "POSTPONED" => "Pospuesto",
            "CANCELLED" => "Cancelado",
            "AWARDED" => "Gano por default",
        ];

        return $translations[$status] ?? $status;
    }

    function traducir_rondas($stage)
    {
        return match($stage) {
            'REGULAR_SEASON' => 'Temporada Regular',
            'GROUP_STAGE' => 'Fase de Grupos',

            'QUALIFICATION' => 'Fase de Clasificación',
            'QUALIFICATION_ROUND_1' => 'Clasificación - Ronda 1',
            'QUALIFICATION_ROUND_2' => 'Clasificación - Ronda 2',
            'QUALIFICATION_ROUND_3' => 'Clasificación - Ronda 3',

            'PLAYOFF_ROUND_1' => 'Playoff - Ronda 1',
            'PLAYOFF_ROUND_2' => 'Playoff - Ronda 2',
            'PLAYOFFS' => 'Playoffs',

            'LAST_32' => 'Dieciseisavos de Final',
            'LAST_16' => 'Octavos de Final',
            'QUARTER_FINALS' => 'Cuartos de Final',
            'SEMI_FINALS' => 'Semifinales',
            'THIRD_PLACE' => 'Tercer Lugar',
            'FINAL' => 'Final',

            default => $stage
        };
    }
