CREATE TABLE IF NOT EXISTS minifedi_actors (
    actorid BIGINT AUTO_INCREMENT PRIMARY KEY,
    username TEXT UNIQUE,
    preferredUsername TEXT,
    name TEXT,
    summary TEXT,
    actortype TEXT DEFAULT 'Person'
);

CREATE TABLE IF NOT EXISTS minifedi_fep_521a_publickeys (
    publickeyid BIGINT AUTO_INCREMENT PRIMARY KEY,
    publickey TEXT,
    actor BIGINT,
    FOREIGN KEY (actor) REFERENCES minifedi_actors (actorid)
);

CREATE TABLE IF NOT EXISTS minifedi_inbox (
    inboxid BIGINT AUTO_INCREMENT PRIMARY KEY,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    read BOOLEAN DEFAULT FALSE,
    actor BIGINT,
    FOREIGN KEY (actor) REFERENCES minifedi_actors (actorid)
);

CREATE TABLE IF NOT EXISTS minifedi_outbox (
    outboxid BIGINT AUTO_INCREMENT PRIMARY KEY,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    read BOOLEAN DEFAULT FALSE,
    actor BIGINT,
    FOREIGN KEY (actor) REFERENCES minifedi_actors (actorid)
);
