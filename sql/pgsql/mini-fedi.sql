CREATE TABLE IF NOT EXISTS minifedi_actors (
    actorid BIGSERIAL PRIMARY KEY,
    username TEXT UNIQUE,
    preferredUsername TEXT,
    name TEXT,
    summary TEXT,
    actortype TEXT DEFAULT 'Person'
);

CREATE TABLE IF NOT EXISTS minifedi_fep_521a_publickeys (
    publickeyid BIGSERIAL PRIMARY KEY,
    publickey TEXT,
    actor BIGINT REFERENCES minifedi_actors (actorid)
);

CREATE TABLE IF NOT EXISTS minifedi_inbox (
    inboxid BIGSERIAL PRIMARY KEY,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    read BOOLEAN DEFAULT FALSE,
    actor BIGINT REFERENCES minifedi_actors (actorid)
);

CREATE TABLE IF NOT EXISTS minifedi_outbox (
    outboxid BIGSERIAL PRIMARY KEY,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    read BOOLEAN DEFAULT FALSE,
    actor BIGINT REFERENCES minifedi_actors (actorid)
);
