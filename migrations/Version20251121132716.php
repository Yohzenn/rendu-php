<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121132716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename category to muscle_group and product to exercise';
    }

    public function up(Schema $schema): void
    {
        // Renommer la table category en muscle_group
        $this->addSql('ALTER TABLE category RENAME TO muscle_group');
        
        // Renommer la table product en exercise
        $this->addSql('ALTER TABLE product RENAME TO exercise');
        
        // Renommer la colonne category_id en muscle_group_id dans exercise
        $this->addSql('ALTER TABLE exercise RENAME COLUMN category_id TO muscle_group_id');
        
        // Renommer l'index de clé étrangère
        $this->addSql('ALTER INDEX IDX_D34A04AD12469DE2 RENAME TO IDX_EXERCISE_MUSCLE_GROUP');
        
        // Renommer la contrainte de clé étrangère
        $this->addSql('ALTER TABLE exercise DROP CONSTRAINT FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_EXERCISE_MUSCLE_GROUP FOREIGN KEY (muscle_group_id) REFERENCES muscle_group (id)');
        
        // Renommer la colonne product_id en exercise_id dans media
        $this->addSql('ALTER TABLE media RENAME COLUMN product_id TO exercise_id');
        
        // Renommer l'index unique dans media
        $this->addSql('ALTER INDEX UNIQ_6A2CA10C4584665A RENAME TO UNIQ_MEDIA_EXERCISE');
        
        // Renommer la contrainte de clé étrangère dans media
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C4584665A');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_MEDIA_EXERCISE FOREIGN KEY (exercise_id) REFERENCES exercise (id)');
    }

    public function down(Schema $schema): void
    {
        // Restaurer la contrainte de clé étrangère dans media
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_MEDIA_EXERCISE');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C4584665A FOREIGN KEY (exercise_id) REFERENCES exercise (id)');
        
        // Restaurer l'index unique dans media
        $this->addSql('ALTER INDEX UNIQ_MEDIA_EXERCISE RENAME TO UNIQ_6A2CA10C4584665A');
        
        // Restaurer la colonne exercise_id en product_id dans media
        $this->addSql('ALTER TABLE media RENAME COLUMN exercise_id TO product_id');
        
        // Restaurer la contrainte de clé étrangère dans exercise
        $this->addSql('ALTER TABLE exercise DROP CONSTRAINT FK_EXERCISE_MUSCLE_GROUP');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (muscle_group_id) REFERENCES muscle_group (id)');
        
        // Restaurer l'index de clé étrangère
        $this->addSql('ALTER INDEX IDX_EXERCISE_MUSCLE_GROUP RENAME TO IDX_D34A04AD12469DE2');
        
        // Restaurer la colonne muscle_group_id en category_id dans exercise
        $this->addSql('ALTER TABLE exercise RENAME COLUMN muscle_group_id TO category_id');
        
        // Restaurer la table exercise en product
        $this->addSql('ALTER TABLE exercise RENAME TO product');
        
        // Restaurer la table muscle_group en category
        $this->addSql('ALTER TABLE muscle_group RENAME TO category');
    }
}
