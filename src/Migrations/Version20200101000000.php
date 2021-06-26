<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE datinglibre.users (
    id UUID NOT NULL PRIMARY KEY,
    email TEXT NOT NULL,
    password TEXT NOT NULL,
    roles TEXT[] NOT NULL,
    ip TEXT,
    enabled boolean NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
    last_login TIMESTAMP WITH TIME ZONE);');
        $this->addSql('CREATE UNIQUE INDEX unique_email_index ON datinglibre.users (LOWER(email));');

        $this->addSql('CREATE TABLE datinglibre.countries (
    id UUID NOT NULL PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);');
        $this->addSql('CREATE TABLE datinglibre.images (
    id UUID NOT NULL PRIMARY KEY,
    type TEXT NOT NULL,
    secure_url TEXT,
    secure_url_expiry TIMESTAMP WITH TIME ZONE,
    is_profile BOOLEAN NOT NULL,
    user_id UUID REFERENCES datinglibre.users ON DELETE SET NULL,
    status TEXT NOT NULL CHECK (status IN (\'UNMODERATED\', \'ACCEPTED\', \'REJECTED\')),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL
);');
        // limit one default profile image per user
        $this->addSql('CREATE UNIQUE INDEX profile_image ON datinglibre.images (user_id) WHERE is_profile IS TRUE;');
        $this->addSql('CREATE TABLE datinglibre.regions (
    id UUID NOT NULL PRIMARY KEY,
    name TEXT NOT NULL,
    country_id UUID NOT NULL REFERENCES datinglibre.countries ON DELETE CASCADE
);');
        $this->addSql('CREATE TABLE datinglibre.cities (
    id UUID NOT NULL PRIMARY KEY,
    geoname_id INTEGER,
    country_id UUID NOT NULL REFERENCES datinglibre.countries ON DELETE CASCADE,
    region_id UUID NOT NULL REFERENCES datinglibre.regions ON DELETE CASCADE,
    name TEXT NOT NULL,
    longitude DOUBLE PRECISION NOT NULL,
    latitude DOUBLE PRECISION NOT NULL
);');
        $this->addSql('CREATE INDEX city_location_index ON datinglibre.cities USING gist (public.geography(public.st_makepoint(longitude, latitude)));');
        $this->addSql('CREATE TABLE datinglibre.profiles (
    user_id UUID NOT NULL PRIMARY KEY REFERENCES datinglibre.users ON DELETE CASCADE,
    username TEXT UNIQUE,
    dob DATE,
    about TEXT,
    meta JSONB,
    city_id UUID REFERENCES datinglibre.cities,
    status TEXT NOT NULL CHECK (status IN (\'UNMODERATED\', \'ACCEPTED\', \'SUSPENDED\', \'PERMANENTLY_SUSPENDED\')),
    sort_id BIGSERIAL NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE
);');
        $this->addSql('CREATE UNIQUE INDEX unique_username_index ON datinglibre.profiles (LOWER(username));');
        $this->addSql('CREATE INDEX profile_sort_order ON datinglibre.profiles(sort_id);');
        $this->addSql('CREATE TABLE datinglibre.categories (
    id UUID NOT NULL PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);');
        $this->addSql('CREATE TABLE datinglibre.attributes (
    id UUID NOT NULL PRIMARY KEY,
    name TEXT UNIQUE,
    category_id UUID NOT NULL REFERENCES datinglibre.categories,
    CONSTRAINT check_attribute_name CHECK (name ~ \'^[a-z_]+$\')
);');
        $this->addSql('CREATE TABLE datinglibre.interests (
    id UUID NOT NULL PRIMARY KEY,
    name TEXT UNIQUE,
    CONSTRAINT check_interest_name CHECK (name ~ \'^[a-z_]+$\')
)');
        $this->addSql('CREATE TABLE datinglibre.user_interests (
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    interest_id UUID NOT NULL REFERENCES datinglibre.interests,
    UNIQUE(user_id, interest_id)
)');
        $this->addSql('CREATE TABLE datinglibre.user_interest_filters (
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    interest_id UUID NOT NULL REFERENCES datinglibre.interests,
    UNIQUE(user_id, interest_id)
 )');

        $this->addSql('CREATE TABLE datinglibre.requirements (
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    attribute_id UUID NOT NULL REFERENCES datinglibre.attributes,
    UNIQUE(user_id, attribute_id)
);');

        $this->addSql('CREATE TABLE datinglibre.user_attributes (
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    attribute_id UUID NOT NULL REFERENCES datinglibre.attributes,
    UNIQUE(user_id, attribute_id)
);');
        $this->addSql('CREATE TABLE datinglibre.blocks (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    blocked_user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    UNIQUE (user_id, blocked_user_id)
);');

        $this->addSql('CREATE TABLE datinglibre.reports (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID NULL REFERENCES datinglibre.users ON DELETE SET NULL,
    reported_user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    reasons TEXT[] NOT NULL,
    message TEXT,
    status TEXT NOT NULL CHECK (status IN (\'OPEN\', \'CLOSED\')),
    user_closed_id UUID REFERENCES datinglibre.users ON DELETE SET NULL,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
    UNIQUE (user_id, reported_user_id)
)');

        $this->addSql('CREATE TABLE datinglibre.filters (
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    region_id UUID REFERENCES datinglibre.regions ON DELETE CASCADE,
    distance INTEGER CHECK (distance > 0),
    min_age INTEGER CHECK (min_age >= 18 AND min_age <= max_age),
    max_age INTEGER CHECK (max_age >= 18 AND max_age >= min_age)
)');
        $this->addSql('CREATE TABLE datinglibre.messages (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    sender_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    content TEXT,
    thread_id UUID NOT NULL, 
    type TEXT,
    sent_time TIMESTAMP WITH TIME ZONE NOT NULL
);');

        $this->addSql('CREATE TABLE datinglibre.tokens (
    id UUID NOT NULL PRIMARY KEY,
    secret TEXT,
    type TEXT,
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
    UNIQUE (user_id, type)
);');

        $this->addSql('CREATE TABLE datinglibre.emails (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES datinglibre.users ON DELETE CASCADE,
    type TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL
);');

        $this->addSql('CREATE TABLE datinglibre.events (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID REFERENCES datinglibre.users ON DELETE SET NULL,
    name TEXT,
    data JSONB,
    sort_id BIGSERIAL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL 
)');
        $this->addSql('CREATE INDEX event_sort_order ON datinglibre.events(sort_id);');

        // the provider_id is the payment provider's subscription ID
        $this->addSql('CREATE TABLE datinglibre.subscriptions (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID REFERENCES datinglibre.users ON DELETE SET NULL,
    provider TEXT NOT NULL,
    provider_subscription_id TEXT NOT NULL,
    status TEXT NOT NULL CHECK (status IN (\'ACTIVE\', \'CANCELLED\', \'RENEWAL_FAILURE\', \'CHARGEBACK\', \'REFUND\')),
    renewal_date DATE NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
    UNIQUE (provider, provider_subscription_id)
)');
        $this->addSql('CREATE INDEX subscriptions_provider_id ON datinglibre.subscriptions(provider, provider_subscription_id);');

        $this->addSql('CREATE TABLE datinglibre.suspensions (
    id UUID NOT NULL PRIMARY KEY,
    user_id UUID REFERENCES datinglibre.users ON DELETE CASCADE,
    user_opened_id UUID REFERENCES datinglibre.users ON DELETE SET NULL,
    user_closed_id UUID REFERENCES datinglibre.users ON DELETE SET NULL,
    duration INT NULL,
    reasons TEXT[] NOT NULL,
    status TEXT NOT NULL CHECK (status IN (\'OPEN\', \'CLOSED\')),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL
)');
        $this->addSql('CREATE UNIQUE INDEX unique_open_suspension ON datinglibre.suspensions(user_id) WHERE (status = \'OPEN\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE datinglibre.profiles');
        $this->addSql('DROP TABLE datinglibre.images');
        $this->addSql('DROP TABLE datinglibre.users;');
        $this->addSql('DROP TABLE datinglibre.cities');
        $this->addSql('DROP TABLE datinglibre.regions');
        $this->addSql('DROP TABLE datinglibre.user_attributes');
        $this->addSql('DROP TABLE datinglibre.attributes');
        $this->addSql('DROP TABLE datinglibre.interests');
        $this->addSql('DROP TABLE datinglibre.user_interests');
        $this->addSql('DROP TABLE datinglibre.user_interest_filters');
        $this->addSql('DROP TABLE datinglibre.blocks');
        $this->addSql('DROP TABLE datinglibre.searches');
        $this->addSql('DROP TABLE datinglibre.messages');
        $this->addSql('DROP TABLE datinglibre.tokens');
        $this->addSql('DROP TABLE datinglibre.emails');
        $this->addSql('DROP TABLE datinglibre.filters');
        $this->addSql('DROP TABLE datinglibre.events');
        $this->addSql('DROP TABLE datinglibre.subscriptions');
        $this->addSql('DROP TABLE datinglibre.reports');
        $this->addSql('DROP TABLE datinglibre.suspensions');
    }
}
