<?php

namespace App\Domain;

final class Lists
{
    public const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    public const URGENCIES = ['LOW', 'MEDIUM', 'HIGH'];
    public const TUNISIA_CITIES = [
        'Ariana',
        'Béja',
        'Ben Arous',
        'Bizerte',
        'Gabès',
        'Gafsa',
        'Jendouba',
        'Kairouan',
        'Kasserine',
        'Kébili',
        'Le Kef',
        'Mahdia',
        'La Manouba',
        'Medenine',
        'Monastir',
        'Nabeul',
        'Sfax',
        'Sidi Bouzid',
        'Siliana',
        'Sousse',
        'Tataouine',
        'Tozeur',
        'Tunis',
        'Zaghouan'
    ];

    public const HOSPITALS_BY_CITY = [
        'Tunis' => [
            'Centre de traumatomogie et des grands brûlés',
            'Centre Hospitalier International, Tunis, Carthagene',
            'Centre national de greffe de moelle osseuse',
            'Clinique Alyssa Tunisienne',
            'Clinique Cardiovasculaire et Generale du Lac',
            'Clinique Montplaisir',
            'Clinique Ohtalmologique du Lac CMA',
            'Clinique Pasteur',
            "Hôpital Aziza Othmana - Tunis",
            "Hôpital Béchir Hamza d'enfants - Tunis",
            "Hôpital Charles Nicolle - Tunis",
            "Hôpital Habib Thameur - Tunis",
            "Hôpital La Rabta - Tunis",
            "Hôpital militaire principal d'instruction - Tunis",
            "Institut Hédi Raïs d'ophtalmologie - Tunis",
            "Institut Salah Azaïz - Tunis",
            "Institut Mohamed Kassab d'orthopédie - Ksar Saïd",
        ],
        'Ariana' => [
            'Clinique El Manar',
            "Hôpital Abderrahmane Mami - Ariana",
        ],
        'Ben Arous' => [
            'Clinique Ennasr',
            'Clinique Taoufik',
            'La Clinique Méditerranéenne',
        ],
        'La Manouba' => [
            'Polyclinique les Jasmins',
        ],

        'Bizerte' => [
            'Hôpital de Menzel Bourguiba',
        ],
        'Nabeul' => [
            'Clinique Les Violettes',
            'Hôpital Mohamed Tlatli - Nabeul',
            "Hôpital Taher Maâmouri - Nabeul",
            'Polyclinique Hammamet',
        ],
        'Sousse' => [
            'Clinique de La Corniche',
            'Clinique Essalem',
            'Hôpital Farhat Hached - Sousse',
            'Hôpital Sahloul - Sousse',
        ],
        'Monastir' => [
            'Centre International Carthage Médical',
            'Hôpital Fattouma Bourguiba - Monastir',
        ],
        'Mahdia' => [
            'Hôpital Taher Sfar - Mahdia',
            "Polyclinique l’Excellence",
        ],
        'Kairouan' => [
            'Hôpital Ibn El Jazzar - Kairouan',
        ],
        'Sfax' => [
            'Hôpital Habib Bourguiba - Sfax',
            'Hôpital Hédi Chaker - Sfax',
            'Polyclinique El Bassatine',
            'Polyclinique Ibn Khaldoun',
        ],
        'Gabès' => [
            'Hôpital Mohamed Sassi - Gabès',
        ],

        'Medenine' => [
            'Pole Hospitalier International Echeifa',
            'Polyclinique Djerba La Douce',
            'Polyclinique Jerba International',
        ],
    ];

    public static function hospitalBelongsToCity(?string $city, ?string $hospital): bool
    {
        if (!$city || !$hospital)
            return false;
        return in_array($hospital, self::HOSPITALS_BY_CITY[$city] ?? [], true);
    }

    public static function allHospitals(): array
    {
        $all = [];
        foreach (self::HOSPITALS_BY_CITY as $hospitals) {
            foreach ($hospitals as $h) {
                $h = trim((string) $h);
                if ($h !== '') {
                    $all[] = $h;
                }
            }
        }

        $all = array_values(array_unique($all));
        sort($all, SORT_NATURAL | SORT_FLAG_CASE);

        return $all;
    }
}