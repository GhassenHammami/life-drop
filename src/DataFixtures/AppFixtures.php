<?php

namespace App\DataFixtures;

use App\Entity\BloodRequest;
use App\Entity\DonationOffer;
use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ---- “Real” local-ish data ----
        $cities = [
            'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Sfax', 'Sousse', 'Nabeul', 'Bizerte',
            'Monastir', 'Mahdia', 'Kairouan', 'Gabès', 'Gafsa', 'Kef', 'Jendouba', 'Zaghouan'
        ];

        $hospitalsByCity = [
            'Tunis' => ['Charles Nicolle', 'Habib Thameur', 'La Rabta', 'Aziza Othmana', 'Mongi Slim (La Marsa)'],
            'Sfax' => ['CHU Hédi Chaker', 'CHU Habib Bourguiba'],
            'Sousse' => ['CHU Sahloul', 'CHU Farhat Hached'],
            'Monastir' => ['CHU Fattouma Bourguiba'],
            'Nabeul' => ['Hôpital Mohamed Taher Maamouri'],
            'Bizerte' => ['Hôpital Régional de Bizerte'],
        ];

        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        // Only 3 states, HIGH is highest
        $urgencies = ['LOW', 'MEDIUM', 'HIGH'];

        $firstNamesM = ['Mohamed', 'Ahmed', 'Youssef', 'Ali', 'Houssem', 'Karim', 'Wassim', 'Sami', 'Mahdi', 'Anis', 'Amine', 'Bilel', 'Hamza', 'Skander', 'Fares'];
        $firstNamesF = ['Fatma', 'Mariem', 'Aya', 'Ines', 'Sarra', 'Yasmin', 'Nour', 'Hiba', 'Rania', 'Asma', 'Imen', 'Amal', 'Chaima', 'Mouna', 'Rim'];
        $lastNames = ['Ben Salah', 'Trabelsi', 'Bouazizi', 'Hammami', 'Mejri', 'Benzarti', 'Jelassi', 'Khelifi', 'Zouari', 'Gharbi', 'Mansouri', 'Baccouche', 'Kefi', 'Lahmar', 'Sghaier'];

        $departments = ['Urgences', 'Réanimation', 'Hématologie', 'Chirurgie', 'Maternité', 'Oncologie', 'Dialyse'];
        $patientContexts = [
            'accident de la route',
            'opération urgente',
            'hémorragie',
            'anémie sévère',
            'traitement de chimiothérapie',
            'accouchement compliqué',
            'intervention chirurgicale'
        ];

        // Offer “types” expressed in message prefixes (schema has message+status only)
        $offerTemplates = [
            'DIRECT' => [
                "Je peux donner aujourd’hui. Je suis {blood}. Disponible à partir de {time}.",
                "Donneur {blood} ici. Je peux passer au centre de transfusion {when}.",
                "Je suis {blood}, prêt à donner. Je suis à {city}. Confirmez svp les détails."
            ],
            'SCHEDULE' => [
                "Je peux donner mais pas aujourd’hui. Disponible {when}.",
                "Je peux me libérer {when}. Est-ce que c’est toujours urgent ?",
                "Je suis dispo {when}. Dites-moi où exactement et la procédure."
            ],
            'FRIEND' => [
                "Je connais 2 personnes {blood} qui peuvent aider. Envoyez-moi un numéro à contacter.",
                "Je peux mobiliser des amis donneurs. Combien d’unités manque-t-il ?",
                "Je partage dans mon entourage. Besoin toujours ouvert ?"
            ],
            'QUESTIONS' => [
                "Quelle est la procédure (carte CIN / RDV) ?",
                "Est-ce que le donneur doit être à jeun ? Quel service à l’hôpital ?",
                "Pouvez-vous préciser le groupe exact et l’endroit (banque du sang) ?"
            ],
            'CANT' => [
                "Désolé je ne peux pas donner (contre-indication). Je peux partager.",
                "Je ne suis pas du bon groupe. Je transfère à des proches.",
                "Je ne peux pas me déplacer, mais je partage l’annonce."
            ],
        ];

        // ---- Helpers ----
        $makePhone = function () use ($faker) {
            $start = $faker->randomElement(['2', '3', '4', '5', '9']);
            return $start . $faker->numerify('#######');
        };

        $randomName = function () use ($faker, $firstNamesM, $firstNamesF, $lastNames) {
            $isMale = $faker->boolean(55);
            $first = $faker->randomElement($isMale ? $firstNamesM : $firstNamesF);
            $last = $faker->randomElement($lastNames);
            return $first . ' ' . $last;
        };

        $pickHospital = function (string $city) use ($faker, $hospitalsByCity) {
            $list = $hospitalsByCity[$city] ?? ['Hôpital Régional', 'Hôpital Universitaire', 'Clinique'];
            $h = $faker->randomElement($list);
            if (str_starts_with($h, 'CHU') || str_starts_with($h, 'Hôpital')) {
                return $h;
            }
            return $faker->boolean(50) ? ('CHU ' . $h) : ('Hôpital ' . $h);
        };

        $timePhrase = function () use ($faker) {
            return $faker->randomElement(['ce matin', 'cet après-midi', 'ce soir', 'demain matin', 'demain', 'après-demain', 'ce week-end']);
        };

        $whenPhrase = function () use ($faker) {
            return $faker->randomElement(['demain', 'après-demain', 'ce week-end', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']);
        };

        // ---- Pools (must exist BEFORE admin creation) ----
        $users = [];
        $donorsByBlood = []; // [bloodType => [User, User, ...]]

        // ---- Admin user (fixed login) ----
        $adminUser = new User();
        $adminUser->setEmail('admin@demo.tn');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));

        $adminProfile = new UserProfile();
        $adminProfile->setFullName('Admin Demo');
        $adminProfile->setPhone('21234567');
        $adminProfile->setCity('Tunis');
        $adminProfile->setBloodType('O+');
        $adminProfile->setAvailable(true);
        $adminProfile->setUser($adminUser);
        $adminUser->setUserProfile($adminProfile);

        $manager->persist($adminUser);
        $manager->persist($adminProfile);

        // include admin in pools
        $users[] = $adminUser;
        $donorsByBlood['O+'] ??= [];
        $donorsByBlood['O+'][] = $adminUser;

        // ---- Create other Users + Profiles ----
        $usersCount = 45;

        for ($i = 0; $i < $usersCount; $i++) {
            $user = new User();
            $email = strtolower($faker->unique()->userName()) . '@example.tn';
            $user->setEmail($email);

            // a few moderators
            if ($i < 3) {
                $user->setRoles(['ROLE_MODERATOR']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

            $profile = new UserProfile();
            $profile->setFullName($randomName());
            $profile->setPhone($makePhone());
            $profile->setCity($faker->randomElement($cities));

            // Most profiles have bloodType, some don’t
            $blood = $faker->boolean(90) ? $faker->randomElement($bloodTypes) : null;
            $profile->setBloodType($blood);

            // Availability influenced by last donation date
            if ($faker->boolean(70)) {
                $lastDonation = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-18 months', 'now'));
                $profile->setLastDonationDate($lastDonation);

                $daysAgo = (new \DateTimeImmutable())->diff($lastDonation)->days ?? 999;
                $available = $daysAgo > 90 ? $faker->boolean(75) : $faker->boolean(25);
                $profile->setAvailable($available);
            } else {
                $profile->setLastDonationDate(null);
                $profile->setAvailable($faker->boolean(70));
            }

            $profile->setUser($user);
            $user->setUserProfile($profile);

            $manager->persist($user);
            $manager->persist($profile);

            $users[] = $user;

            if ($blood !== null) {
                $donorsByBlood[$blood] ??= [];
                $donorsByBlood[$blood][] = $user;
            }
        }

        // ---- Create Blood Requests ----
        $requests = [];
        $requestsCount = 70;

        for ($i = 0; $i < $requestsCount; $i++) {
            $req = new BloodRequest();

            $city = $faker->randomElement($cities);
            $hospital = $pickHospital($city);

            $bloodType = $faker->randomElement($bloodTypes);

            // More realistic: O- tends to be more urgent, but max is HIGH
            $baseUrgency = $faker->randomElement($urgencies);
            if ($bloodType === 'O-' && $faker->boolean(55)) {
                $baseUrgency = 'HIGH';
            }

            $units = match ($baseUrgency) {
                'LOW' => $faker->numberBetween(1, 2),
                'MEDIUM' => $faker->numberBetween(1, 3),
                'HIGH' => $faker->numberBetween(2, 6),
                default => $faker->numberBetween(1, 4),
            };

            $dept = $faker->randomElement($departments);
            $context = $faker->randomElement($patientContexts);

            $description = $faker->randomElement([
                "Besoin urgent de sang {$bloodType} pour {$context}. Service: {$dept}.",
                "Demande {$bloodType} - {$dept}. {$context}. Merci de contacter rapidement.",
                "Urgent {$bloodType}: {$units} unités. {$dept}, {$hospital}.",
                "Recherche donneurs {$bloodType}. Contexte: {$context}. {$dept}.",
            ]);

            $createdAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-60 days', 'now'));

            $req->setBloodType($bloodType);
            $req->setHospitalName($hospital);
            $req->setCity($city);
            $req->setUnitsNeeded($units);
            $req->setUrgency($baseUrgency);
            $req->setDescription($faker->boolean(85) ? $description : null);
            $req->setContactPhone($makePhone());
            $req->setCreatedAt($createdAt);

            // Make admin creator for first 2 requests
            if ($i < 2) {
                $req->setCreatedBy($adminUser);
            } else {
                $req->setCreatedBy($faker->randomElement($users));
            }

            $req->setStatus('OPEN');

            if ($faker->boolean(55)) {
                $req->setUpdatedAt($createdAt->modify('+' . $faker->numberBetween(1, 20) . ' days'));
            }

            $manager->persist($req);
            $requests[] = $req;
        }

        // ---- Create Donation Offers (realistic flow) ----
        // Make admin appear as donor for a fixed number of offers
        $adminOffersLeft = 8;

        foreach ($requests as $req) {
            $blood = $req->getBloodType();

            $offersCount = match ($req->getUrgency()) {
                'LOW' => random_int(0, 2),
                'MEDIUM' => random_int(1, 4),
                'HIGH' => random_int(2, 8),
                default => random_int(1, 5),
            };

            $poolMatch = $donorsByBlood[$blood] ?? [];
            $poolAny = $users;

            $acceptedCount = 0;

            for ($j = 0; $j < $offersCount; $j++) {
                $offer = new DonationOffer();
                $offer->setRequest($req);

                // pick donor: 70% matching group if possible
                if (!empty($poolMatch) && $faker->boolean(70)) {
                    $donor = $faker->randomElement($poolMatch);
                } else {
                    $donor = $faker->randomElement($poolAny);
                }

                // Force admin to be donor for some offers, but never on his own requests
                if ($adminOffersLeft > 0 && $req->getCreatedBy() !== $adminUser && $faker->boolean(35)) {
                    $donor = $adminUser;
                    $adminOffersLeft--;
                }

                // avoid request creator as donor
                if ($donor === $req->getCreatedBy()) {
                    $donor = $faker->randomElement($poolAny);
                }

                $offer->setDonor($donor);

                $offerType = $faker->randomElement(['DIRECT', 'DIRECT', 'SCHEDULE', 'FRIEND', 'QUESTIONS', 'CANT']);
                $template = $faker->randomElement($offerTemplates[$offerType]);

                $msg = strtr($template, [
                    '{blood}' => $blood,
                    '{city}' => $faker->randomElement($cities),
                    '{time}' => $timePhrase(),
                    '{when}' => $whenPhrase(),
                ]);

                $offer->setMessage("[{$offerType}] " . $msg);

                // status logic
                $status = 'PENDING';
                if ($offerType === 'DIRECT') {
                    $status = $faker->randomElement(['PENDING', 'ACCEPTED', 'ACCEPTED', 'REJECTED']);
                } elseif ($offerType === 'SCHEDULE') {
                    $status = $faker->randomElement(['PENDING', 'PENDING', 'ACCEPTED', 'REJECTED']);
                } elseif ($offerType === 'QUESTIONS') {
                    $status = $faker->randomElement(['PENDING', 'PENDING', 'PENDING', 'REJECTED']);
                } elseif ($offerType === 'FRIEND') {
                    $status = $faker->randomElement(['PENDING', 'PENDING', 'ACCEPTED', 'REJECTED']);
                } elseif ($offerType === 'CANT') {
                    $status = $faker->randomElement(['REJECTED', 'CANCELLED', 'PENDING']);
                }

                if ($status === 'ACCEPTED' && $acceptedCount >= 2) {
                    $status = $faker->randomElement(['PENDING', 'REJECTED']);
                }

                $offer->setStatus($status);

                $offerCreatedAt = $req->getCreatedAt()->modify('+' . $faker->numberBetween(0, 10) . ' days');
                $offer->setCreatedAt($offerCreatedAt);

                if ($status === 'ACCEPTED') {
                    $acceptedCount++;
                }

                $manager->persist($offer);
            }

            // close some requests based on accepted offers or age
            $ageDays = (new \DateTimeImmutable())->diff($req->getCreatedAt())->days ?? 0;

            if ($acceptedCount > 0 && $faker->boolean(75)) {
                $req->setStatus('FULFILLED');
                $req->setUpdatedAt($req->getCreatedAt()->modify('+' . $faker->numberBetween(1, 12) . ' days'));
            } elseif ($ageDays > 25 && $faker->boolean(40)) {
                $req->setStatus('CANCELLED');
                $req->setUpdatedAt($req->getCreatedAt()->modify('+' . $faker->numberBetween(10, 30) . ' days'));
            } else {
                if ($faker->boolean(60)) {
                    $req->setUpdatedAt($req->getCreatedAt()->modify('+' . $faker->numberBetween(1, 20) . ' days'));
                }
            }
        }

        $manager->flush();
    }
}
