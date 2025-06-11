CREATE TABLE "post_offices" (
  "id" serial PRIMARY KEY NOT NULL,
  "acores_id" varchar,
  "parent_office_acores_id" varchar,
  "name" varchar NOT NULL,
  "site_type" varchar,
  "street_address" text NOT NULL,
  "address_complement" text,
  "postal_code" varchar(10) NOT NULL,
  "city" varchar NOT NULL,
  "insee_code" varchar(5),
  "country" varchar NOT NULL DEFAULT 'France',
  "latitude" decimal(10,8),
  "longitude" decimal(11,8),
  "phone_number" varchar(20),
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "delivery_companies" (
  "id" serial PRIMARY KEY NOT NULL,
  "company_name" varchar(100) UNIQUE NOT NULL,
  "contact_email" varchar(255),
  "contact_phone" varchar(20),
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "delivery_personnel" (
  "id" serial PRIMARY KEY NOT NULL,
  "first_name" varchar(50) NOT NULL,
  "last_name" varchar(50) NOT NULL,
  "email" varchar(255) UNIQUE NOT NULL,
  "phone_number" varchar(20),
  "primary_office_id" integer NOT NULL,
  "company_id" integer NOT NULL,
  "hire_date" date NOT NULL,
  "is_active" boolean DEFAULT true,
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "clients" (
  "id" serial PRIMARY KEY NOT NULL,
  "first_name" varchar(50) NOT NULL,
  "last_name" varchar(50) NOT NULL,
  "account_email" varchar(255) UNIQUE NOT NULL,
  "account_phone_number" varchar(20) UNIQUE,
  "account_password_hash" varchar(255) NOT NULL,
  "default_address" text,
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "shops" (
  "id" serial PRIMARY KEY NOT NULL,
  "name" varchar(255) NOT NULL,
  "category_id" integer,
  "description" text,
  "owner_id" integer,
  "address" text NOT NULL,
  "address_complement" text,
  "city" varchar(100) NOT NULL,
  "postal_code" varchar(10) NOT NULL,
  "country" varchar(50) NOT NULL DEFAULT 'France',
  "latitude" decimal(10,8),
  "longitude" decimal(11,8),
  "phone_number" varchar(20),
  "email" varchar(255),
  "website" varchar(255),
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "shop_categories" (
  "id" serial PRIMARY KEY NOT NULL,
  "category_name" varchar(50) UNIQUE NOT NULL
);

CREATE TABLE "shop_owners" (
  "id" serial PRIMARY KEY NOT NULL,
  "first_name" varchar(50) NOT NULL,
  "last_name" varchar(50) NOT NULL,
  "account_email" varchar(255) UNIQUE NOT NULL,
  "account_phone_number" varchar(20) UNIQUE,
  "account_password_hash" varchar(255) NOT NULL,
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "package_statuses" (
  "id" serial PRIMARY KEY NOT NULL,
  "status_code" varchar(20) UNIQUE NOT NULL,
  "status_name" varchar(50) UNIQUE NOT NULL,
  "description" text,
  "is_final_status" boolean DEFAULT false,
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "packages" (
  "id" serial PRIMARY KEY NOT NULL,
  "tracking_number" varchar(20) UNIQUE NOT NULL,
  "current_status_id" integer NOT NULL,
  "current_office_id" integer,
  "recipient_client_id" integer NOT NULL,
  "onpackage_sender_name" varchar(100) NOT NULL,
  "onpackage_sender_address" text NOT NULL,
  "sender_shop_id" integer NOT NULL,
  "onpackage_recipient_name" varchar(100) NOT NULL,
  "onpackage_destination_address" text NOT NULL,
  "weight_kg" decimal(8,3),
  "dimensions_cm" jsonb,
  "volume_m3" decimal(10,6),
  "is_priority" boolean DEFAULT false,
  "is_fragile" boolean DEFAULT false,
  "declared_value" decimal(10,2),
  "estimated_delivery_date" date,
  "actual_delivery_date" timestamp,
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "updated_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE "transit_events" (
  "id" serial PRIMARY KEY NOT NULL,
  "package_id" integer NOT NULL,
  "event_timestamp" timestamp DEFAULT (CURRENT_TIMESTAMP),
  "status_id" integer NOT NULL,
  "office_id" integer,
  "delivery_person_id" integer,
  "event_sequence" integer NOT NULL,
  "previous_event_id" integer,
  "event_description" text,
  "exception_reason" varchar(255),
  "signature_required" boolean DEFAULT false,
  "signature_obtained" boolean DEFAULT false,
  "recipient_name" varchar(100),
  "created_at" timestamp DEFAULT (CURRENT_TIMESTAMP)
);

COMMENT ON COLUMN "post_offices"."parent_office_acores_id" IS 'Reference to parent/main office';

COMMENT ON COLUMN "post_offices"."name" IS 'Post office name';

COMMENT ON COLUMN "post_offices"."site_type" IS 'Type/category of the site';

COMMENT ON COLUMN "post_offices"."address_complement" IS 'Additional address information';

COMMENT ON COLUMN "post_offices"."locality" IS 'District or area name';

COMMENT ON COLUMN "post_offices"."insee_code" IS 'French INSEE municipality code';

COMMENT ON COLUMN "post_offices"."latitude" IS 'GPS latitude coordinate';

COMMENT ON COLUMN "post_offices"."longitude" IS 'GPS longitude coordinate';

COMMENT ON COLUMN "post_offices"."phone_number" IS 'Contact phone number';

COMMENT ON COLUMN "delivery_personnel"."employee_id" IS 'Company employee ID';

COMMENT ON COLUMN "clients"."account_email" IS 'Must be a valid email address';

COMMENT ON COLUMN "clients"."account_phone_number" IS 'International format preferred';

COMMENT ON COLUMN "clients"."account_password_hash" IS 'Argon2 password hash';

COMMENT ON COLUMN "shops"."id" IS 'Unique shop identifier';

COMMENT ON COLUMN "shops"."name" IS 'Shop name';

COMMENT ON COLUMN "shops"."category_id" IS 'e.g., Clothing, Food, Electronics';

COMMENT ON COLUMN "shops"."description" IS 'Detailed shop description';

COMMENT ON COLUMN "shops"."owner_id" IS 'Reference to owner (if applicable)';

COMMENT ON COLUMN "shops"."address_complement" IS 'Apartment, suite, etc.';

COMMENT ON COLUMN "shops"."latitude" IS 'GPS latitude coordinate';

COMMENT ON COLUMN "shops"."longitude" IS 'GPS longitude coordinate';

COMMENT ON COLUMN "shops"."phone_number" IS 'Contact phone number';

COMMENT ON COLUMN "shops"."email" IS 'Contact email address';

COMMENT ON COLUMN "shops"."website" IS 'Shop website URL';

COMMENT ON COLUMN "shop_owners"."account_email" IS 'Must be a valid email address';

COMMENT ON COLUMN "shop_owners"."account_phone_number" IS 'International format preferred';

COMMENT ON COLUMN "shop_owners"."account_password_hash" IS 'Argon2 password hash';

COMMENT ON COLUMN "package_statuses"."status_code" IS 'Short status code';

COMMENT ON COLUMN "package_statuses"."status_name" IS 'Human//readable status';

COMMENT ON COLUMN "package_statuses"."description" IS 'Detailed status description';

COMMENT ON COLUMN "package_statuses"."is_final_status" IS 'Indicates if this is an end state';

COMMENT ON COLUMN "packages"."tracking_number" IS 'External tracking identifier';

COMMENT ON COLUMN "packages"."weight_kg" IS 'Weight in kilograms';

COMMENT ON COLUMN "packages"."dimensions_cm" IS 'Length, width, height in cm';

COMMENT ON COLUMN "packages"."volume_m3" IS 'Calculated volume in cubic meters';

COMMENT ON COLUMN "packages"."declared_value" IS 'Declared value for insurance';

COMMENT ON COLUMN "transit_events"."office_id" IS 'Office where event occurred';

COMMENT ON COLUMN "transit_events"."delivery_person_id" IS 'Personnel handling the package';

COMMENT ON COLUMN "transit_events"."event_sequence" IS 'Order of events for this package';

COMMENT ON COLUMN "transit_events"."previous_event_id" IS 'Reference to previous event';

COMMENT ON COLUMN "transit_events"."event_description" IS 'Detailed event information';

COMMENT ON COLUMN "transit_events"."exception_reason" IS 'Reason for delays or issues';

COMMENT ON COLUMN "transit_events"."recipient_name" IS 'Who received the package';

ALTER TABLE "delivery_personnel" ADD FOREIGN KEY ("primary_office_id") REFERENCES "post_offices" ("id");

ALTER TABLE "delivery_personnel" ADD FOREIGN KEY ("company_id") REFERENCES "delivery_companies" ("id");

ALTER TABLE "shops" ADD FOREIGN KEY ("category_id") REFERENCES "shop_categories" ("id");

ALTER TABLE "shops" ADD FOREIGN KEY ("owner_id") REFERENCES "shop_owners" ("id");

ALTER TABLE "packages" ADD FOREIGN KEY ("recipient_client_id") REFERENCES "clients" ("id");

ALTER TABLE "packages" ADD FOREIGN KEY ("current_status_id") REFERENCES "package_statuses" ("id");

ALTER TABLE "packages" ADD FOREIGN KEY ("current_office_id") REFERENCES "post_offices" ("id");

ALTER TABLE "packages" ADD FOREIGN KEY ("sender_shop_id") REFERENCES "shops" ("id");

ALTER TABLE "transit_events" ADD FOREIGN KEY ("package_id") REFERENCES "packages" ("id");

ALTER TABLE "transit_events" ADD FOREIGN KEY ("status_id") REFERENCES "package_statuses" ("id");

ALTER TABLE "transit_events" ADD FOREIGN KEY ("office_id") REFERENCES "post_offices" ("id");

ALTER TABLE "transit_events" ADD FOREIGN KEY ("delivery_person_id") REFERENCES "delivery_personnel" ("id");

ALTER TABLE "transit_events" ADD FOREIGN KEY ("previous_event_id") REFERENCES "transit_events" ("id");
