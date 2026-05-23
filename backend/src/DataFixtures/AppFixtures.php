<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Entity\Comment;
use App\Domain\Entity\Conversation;
use App\Domain\Entity\FeedReport;
use App\Domain\Entity\Message;
use App\Domain\Entity\MessageHiddenForUser;
use App\Domain\Entity\Post;
use App\Domain\Entity\PostReaction;
use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Entity\UserProfile;
use App\Domain\Entity\UserWarning;
use App\Domain\ValueObject\FeedReportDecision;
use App\Domain\ValueObject\FeedReportReason;
use App\Domain\ValueObject\PostReactionType;
use App\Domain\ValueObject\SystemNotificationType;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    private const DEMO_PASSWORD = 'DemoPassword123';

    /**
     * @var array<string, User>
     */
    private array $users = [];

    /**
     * @var array<string, Post>
     */
    private array $posts = [];

    /**
     * @var array<string, Comment>
     */
    private array $comments = [];

    /**
     * @var array<string, Conversation>
     */
    private array $conversations = [];

    /**
     * @var array<string, list<Message>>
     */
    private array $messages = [];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUsers($manager);
        $this->createFeed($manager);
        $this->createConversations($manager);

        // Flush once here so generated ids are available for UserWarning content references.
        $manager->flush();

        $this->createReportsWarningsAndNotifications($manager);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): void
    {
        $profiles = [
            'admin' => [
                'email' => 'admin@leklub.test',
                'username' => 'admin_leklub',
                'roles' => ['ROLE_ADMIN'],
                'displayName' => 'Admin LeKlub',
                'bio' => 'Supervision du MVP, modération du Feed et démonstration CDA.',
                'favoriteTeamName' => 'Équipe de France',
            ],
            'moderator' => [
                'email' => 'moderateur@leklub.test',
                'username' => 'moderateur_klub',
                'roles' => ['ROLE_ADMIN'],
                'displayName' => 'Modérateur Klub',
                'bio' => 'Compte admin secondaire pour illustrer les décisions de modération.',
                'favoriteTeamName' => 'RC Lens',
            ],
            'samuel' => [
                'email' => 'samuel.demo@leklub.test',
                'username' => 'samuel_demo',
                'roles' => [],
                'displayName' => 'Samuel Démo',
                'bio' => 'Supporter curieux, fan de débats tactiques et de Ligue 1.',
                'favoriteTeamName' => 'Paris Saint-Germain',
            ],
            'marie' => [
                'email' => 'marie.foot@leklub.test',
                'username' => 'marie_foot',
                'roles' => [],
                'displayName' => 'Marie Foot',
                'bio' => 'Toujours partante pour discuter ambiance stade et jeunes talents.',
                'favoriteTeamName' => 'Olympique Lyonnais',
            ],
            'yanis' => [
                'email' => 'yanis.ultra@leklub.test',
                'username' => 'yanis_ultra',
                'roles' => [],
                'displayName' => 'Yanis Ultra',
                'bio' => 'Virages, chants et débats passionnés autour de l’OM.',
                'favoriteTeamName' => 'Olympique de Marseille',
            ],
            'clara' => [
                'email' => 'clara.tactique@leklub.test',
                'username' => 'clara_tactique',
                'roles' => [],
                'displayName' => 'Clara Tactique',
                'bio' => 'Analyse des systèmes de jeu, pressing et sorties de balle.',
                'favoriteTeamName' => 'FC Barcelone',
            ],
            'leo' => [
                'email' => 'leo.pl@leklub.test',
                'username' => 'leo_premierleague',
                'roles' => [],
                'displayName' => 'Léo Premier League',
                'bio' => 'Regarde trop de matchs anglais et assume totalement.',
                'favoriteTeamName' => 'Arsenal',
            ],
            'nina' => [
                'email' => 'nina.suspendue@leklub.test',
                'username' => 'nina_suspendue',
                'roles' => [],
                'displayName' => 'Nina Suspendue',
                'bio' => 'Compte de démonstration pour le blocage des actions d’écriture.',
                'favoriteTeamName' => 'Juventus',
                'suspendedUntil' => (new DateTimeImmutable())->add(new DateInterval('P7D')),
            ],
        ];

        foreach ($profiles as $key => $data) {
            $user = new User($data['email'], $data['username'], 'temporary');
            $user->setPassword($this->passwordHasher->hashPassword($user, self::DEMO_PASSWORD));
            $user->setRoles($data['roles']);

            if (isset($data['suspendedUntil'])) {
                $user->suspendUntil($data['suspendedUntil']);
            }

            $profile = new UserProfile($user);
            $profile->update(
                $data['displayName'],
                $data['bio'],
                $data['favoriteTeamName'],
                null
            );

            $this->users[$key] = $user;

            $manager->persist($user);
            $manager->persist($profile);
        }
    }

    private function createFeed(ObjectManager $manager): void
    {
        $postDefinitions = [
            'psg_pressing' => ['samuel', 'Le pressing du PSG a changé le match : récupération haute, transitions rapides et beaucoup plus de présence entre les lignes.'],
            'lyon_youth' => ['marie', 'Très envie de voir davantage de jeunes lyonnais dans la rotation. Sur 20 minutes, ils ont amené une vraie intensité.'],
            'om_stadium' => ['yanis', 'L’ambiance au Vélodrome reste un avantage énorme. Même quand le match est fermé, ça pousse l’équipe à jouer plus haut.'],
            'barca_shape' => ['clara', 'Le 3-2-5 à la relance fonctionne seulement si les latéraux lisent bien les espaces. Sinon, la perte de balle devient dangereuse.'],
            'arsenal_depth' => ['leo', 'Arsenal a enfin un banc capable de changer le rythme. C’est peut-être ce qui manquait sur les fins de saison précédentes.'],
            'ligue1_table' => ['samuel', 'La Ligue 1 est plus serrée qu’on le dit. Les confrontations directes vont peser très lourd dans le sprint final.'],
            'keeper_form' => ['marie', 'Le gardien qui sort deux arrêts décisifs en fin de match, c’est presque aussi fort qu’un but.'],
            'derby_energy' => ['yanis', 'Un derby ne se joue jamais uniquement sur le classement. L’intensité et les duels racontent souvent une autre histoire.'],
            'deleted_post' => ['nina', 'Achetez des maillots contrefaits ici, livraison rapide partout.'],
            'second_deleted_post' => ['nina', 'Message volontairement provocateur supprimé par la modération dans la démonstration.'],
        ];

        foreach ($postDefinitions as $key => [$authorKey, $content]) {
            $post = new Post($this->users[$authorKey], $content);
            $this->posts[$key] = $post;
            $manager->persist($post);
        }

        $this->posts['deleted_post']->delete($this->users['moderator']);
        $this->posts['second_deleted_post']->delete($this->users['admin']);

        $commentDefinitions = [
            'c1' => ['psg_pressing', 'clara', 'Oui, surtout parce que le bloc a accompagné les courses. Avant, les lignes étaient trop étirées.'],
            'c2' => ['psg_pressing', 'leo', 'Le deuxième ballon gagné avant le but résume tout le changement.'],
            'c3' => ['lyon_youth', 'samuel', 'Ils méritent du temps, mais il faut les protéger dans les gros matchs.'],
            'c4' => ['lyon_youth', 'yanis', 'Le talent est là, le plus dur sera la régularité.'],
            'c5' => ['om_stadium', 'marie', 'On sent vraiment la différence à la TV, alors au stade j’imagine même pas.'],
            'c6' => ['barca_shape', 'samuel', 'Quand le six décroche au bon moment, ça ouvre tout le terrain.'],
            'c7' => ['barca_shape', 'leo', 'Le risque, c’est la transition défensive si le ballon sort dans l’axe.'],
            'c8' => ['arsenal_depth', 'clara', 'La profondeur de banc change aussi la pression sur les titulaires.'],
            'c9' => ['arsenal_depth', 'marie', 'Je trouve surtout que les remplaçants gardent le même niveau technique.'],
            'c10' => ['ligue1_table', 'yanis', 'Les matchs à l’extérieur vont faire la différence.'],
            'c11' => ['keeper_form', 'leo', 'Un clean sheet comme ça, ça vaut presque une passe décisive.'],
            'c12' => ['derby_energy', 'clara', 'Les duels au milieu seront la clé.'],
            'c13' => ['derby_energy', 'samuel', 'Exactement, le premier quart d’heure donnera le ton.'],
            'c14' => ['psg_pressing', 'nina', 'Commentaire agressif supprimé pour illustrer la modération.'],
            'c15' => ['om_stadium', 'nina', 'Insulte volontairement retirée par un administrateur.'],
            'c16' => ['barca_shape', 'nina', 'Spam supprimé dans la démonstration admin.'],
            'c17' => ['arsenal_depth', 'samuel', 'Le mercato devient enfin cohérent avec le projet de jeu.'],
            'c18' => ['keeper_form', 'clara', 'Son placement avant la frappe est parfait.'],
        ];

        foreach ($commentDefinitions as $key => [$postKey, $authorKey, $content]) {
            $comment = new Comment($this->posts[$postKey], $this->users[$authorKey], $content);
            $this->comments[$key] = $comment;
            $manager->persist($comment);
        }

        $this->comments['c14']->delete($this->users['moderator']);
        $this->comments['c15']->delete($this->users['admin']);
        $this->comments['c16']->delete($this->users['moderator']);

        $reactionDefinitions = [
            ['psg_pressing', 'marie', PostReactionType::Like],
            ['psg_pressing', 'clara', PostReactionType::Like],
            ['psg_pressing', 'leo', PostReactionType::Like],
            ['lyon_youth', 'samuel', PostReactionType::Like],
            ['lyon_youth', 'yanis', PostReactionType::Dislike],
            ['om_stadium', 'samuel', PostReactionType::Like],
            ['om_stadium', 'marie', PostReactionType::Like],
            ['om_stadium', 'leo', PostReactionType::Dislike],
            ['barca_shape', 'samuel', PostReactionType::Like],
            ['barca_shape', 'marie', PostReactionType::Like],
            ['barca_shape', 'leo', PostReactionType::Like],
            ['arsenal_depth', 'samuel', PostReactionType::Like],
            ['arsenal_depth', 'clara', PostReactionType::Like],
            ['arsenal_depth', 'yanis', PostReactionType::Dislike],
            ['ligue1_table', 'marie', PostReactionType::Like],
            ['ligue1_table', 'yanis', PostReactionType::Like],
            ['keeper_form', 'samuel', PostReactionType::Like],
            ['keeper_form', 'leo', PostReactionType::Like],
            ['derby_energy', 'samuel', PostReactionType::Like],
            ['derby_energy', 'marie', PostReactionType::Like],
            ['derby_energy', 'clara', PostReactionType::Like],
            ['derby_energy', 'leo', PostReactionType::Dislike],
        ];

        foreach ($reactionDefinitions as [$postKey, $userKey, $type]) {
            $manager->persist(new PostReaction($this->posts[$postKey], $this->users[$userKey], $type));
        }
    }

    private function createConversations(ObjectManager $manager): void
    {
        $this->createConversation($manager, 'samuel_marie', 'samuel', 'marie', [
            ['samuel', 'Tu as vu le match de Lyon hier ? Les jeunes ont vraiment changé le rythme.', true],
            ['marie', 'Oui, et le public a suivi direct. Ça peut devenir un vrai levier pour la fin de saison.', true],
            ['samuel', 'Je vais relancer le sujet dans le Feed après le prochain match.', false],
            ['marie', 'Bonne idée, je répondrai avec deux noms à suivre côté formation.', false],
            ['samuel', 'Parfait, ça donnera un débat plus concret que juste parler du score.', false],
        ]);

        $this->createConversation($manager, 'samuel_clara', 'samuel', 'clara', [
            ['clara', 'J’ai revu la séquence sur le pressing, c’est encore plus net au ralenti.', true],
            ['samuel', 'Tu peux me l’envoyer ? Je veux comprendre le déclencheur exact.', true],
            ['clara', 'Je te prépare un résumé simple ce soir.', false],
            ['samuel', 'Merci, je veux surtout voir le rôle du milieu côté ballon.', true],
            ['clara', 'C’est justement lui qui ferme la passe intérieure avant la récupération.', false],
        ]);

        $this->createConversation($manager, 'samuel_leo', 'samuel', 'leo', [
            ['leo', 'Arsenal joue dimanche, je sens encore un match piège.', true],
            ['samuel', 'Le banc peut faire la différence cette fois.', false],
            ['leo', 'Oui, surtout si le match reste bloqué après l’heure de jeu.', false],
            ['samuel', 'Je surveillerai les changements, ça raconte souvent le niveau de confiance du coach.', false],
            ['leo', 'On en reparle après le match, je sens que le coaching sera décisif.', false],
        ]);

        $this->createConversation($manager, 'yanis_marie', 'yanis', 'marie', [
            ['yanis', 'Même toi tu dois reconnaître que l’ambiance à Marseille était folle.', true],
            ['marie', 'Je reconnais, mais je garde Lyon devant pour la formation.', true],
            ['yanis', 'Formation oui, mais sur un match couperet je prends le Vélodrome.', true],
            ['marie', 'On va dire que chaque club garde son argument préféré.', true],
        ]);

        $this->createConversation($manager, 'admin_nina', 'admin', 'nina', [
            ['admin', 'Votre compte est temporairement limité suite à plusieurs avertissements de modération.', true],
            ['nina', 'Compris, je vais relire les règles avant de republier.', true],
            ['admin', 'La lecture reste disponible. Les actions d’écriture reprendront après la suspension.', true],
            ['nina', 'Merci pour la précision, je ferai attention au ton de mes prochains messages.', true],
            ['admin', 'L’objectif est de garder les échanges football intéressants pour tout le monde.', true],
        ]);

        $manager->persist(new MessageHiddenForUser(
            $this->messages['samuel_marie'][0],
            $this->users['samuel']
        ));
        $manager->persist(new MessageHiddenForUser(
            $this->messages['samuel_clara'][1],
            $this->users['samuel']
        ));
    }

    /**
     * @param list<array{0: string, 1: string, 2: bool}> $messages
     */
    private function createConversation(ObjectManager $manager, string $key, string $firstUserKey, string $secondUserKey, array $messages): void
    {
        $conversation = new Conversation($this->users[$firstUserKey], $this->users[$secondUserKey]);
        $this->conversations[$key] = $conversation;
        $this->messages[$key] = [];
        $manager->persist($conversation);

        foreach ($messages as [$senderKey, $content, $read]) {
            $message = new Message($conversation, $this->users[$senderKey], $content);

            if ($read) {
                $message->markAsRead();
            }

            $manager->persist($message);
            $this->messages[$key][] = $message;
        }
    }

    private function createReportsWarningsAndNotifications(ObjectManager $manager): void
    {
        $openPostReport = FeedReport::forPost(
            $this->users['marie'],
            $this->posts['derby_energy'],
            FeedReportReason::Other,
            'Débat très tendu dans les commentaires, à surveiller.'
        );

        $openCommentReport = FeedReport::forComment(
            $this->users['leo'],
            $this->comments['c10'],
            FeedReportReason::Harassment,
            'Le ton commence à devenir personnel.'
        );

        $rejectedReport = FeedReport::forPost(
            $this->users['yanis'],
            $this->posts['barca_shape'],
            FeedReportReason::InappropriateContent,
            'Je ne suis pas d’accord avec l’analyse.'
        );
        $rejectedReport->resolve(
            $this->users['moderator'],
            FeedReportDecision::Rejected,
            'Opinion footballistique recevable, pas de violation.'
        );

        $removedPostReport = FeedReport::forPost(
            $this->users['samuel'],
            $this->posts['deleted_post'],
            FeedReportReason::Spam,
            'Lien commercial douteux et maillots contrefaits.'
        );
        $removedPostReport->resolve(
            $this->users['moderator'],
            FeedReportDecision::ContentRemoved,
            'Spam commercial supprimé.'
        );

        $removedCommentReport = FeedReport::forComment(
            $this->users['clara'],
            $this->comments['c15'],
            FeedReportReason::Insults,
            'Commentaire insultant dans une discussion publique.'
        );
        $removedCommentReport->resolve(
            $this->users['admin'],
            FeedReportDecision::ContentRemoved,
            'Insulte supprimée.'
        );

        $spamCommentReport = FeedReport::forComment(
            $this->users['marie'],
            $this->comments['c16'],
            FeedReportReason::Spam,
            'Spam répété dans une discussion tactique.'
        );
        $spamCommentReport->resolve(
            $this->users['moderator'],
            FeedReportDecision::ContentRemoved,
            'Spam répété supprimé.'
        );

        foreach ([$openPostReport, $openCommentReport, $rejectedReport, $removedPostReport, $removedCommentReport, $spamCommentReport] as $report) {
            $manager->persist($report);
        }

        $manager->flush();

        $warnings = [
            new UserWarning($this->users['nina'], $removedPostReport, $this->users['moderator'], 'post', (int) $this->posts['deleted_post']->getId(), 'Spam commercial dans le Feed.'),
            new UserWarning($this->users['nina'], $removedCommentReport, $this->users['admin'], 'comment', (int) $this->comments['c15']->getId(), 'Insulte envers un autre membre.'),
            new UserWarning($this->users['nina'], $spamCommentReport, $this->users['moderator'], 'comment', (int) $this->comments['c16']->getId(), 'Répétition de contenus de spam.'),
        ];

        foreach ($warnings as $warning) {
            $manager->persist($warning);
        }

        $notifications = [
            new SystemNotification($this->users['yanis'], SystemNotificationType::ReportRejected, 'Signalement traité', "Votre signalement a été étudié. Il n'a pas été retenu par la modération."),
            new SystemNotification($this->users['samuel'], SystemNotificationType::ReportAccepted, 'Signalement confirmé', 'Merci pour votre signalement. Le contenu concerné a été supprimé.'),
            new SystemNotification($this->users['clara'], SystemNotificationType::ReportAccepted, 'Signalement confirmé', 'Merci pour votre signalement. Le commentaire concerné a été supprimé.'),
            new SystemNotification($this->users['nina'], SystemNotificationType::Warning, 'Avertissement', 'Un de vos contenus a été supprimé suite à un signalement validé.'),
            new SystemNotification($this->users['nina'], SystemNotificationType::Suspension, 'Compte temporairement suspendu', 'Votre compte est temporairement suspendu après plusieurs avertissements.'),
            new SystemNotification($this->users['moderator'], SystemNotificationType::AdminRoleGranted, 'Accès administrateur', 'Votre compte dispose des accès de modération pour la démonstration.'),
            new SystemNotification($this->users['samuel'], SystemNotificationType::UserUnsuspended, 'Compte actif', 'Votre compte est actif et peut publier dans LeKlub.'),
            new SystemNotification($this->users['marie'], SystemNotificationType::ReportRejected, 'Information modération', 'Un signalement de démonstration reste ouvert pour illustrer la file admin.'),
        ];

        $notifications[0]->markAsRead();
        $notifications[2]->markAsRead();
        $notifications[5]->markAsRead();

        foreach ($notifications as $notification) {
            $manager->persist($notification);
        }
    }

}
