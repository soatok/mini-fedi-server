CREATE TABLE IF NOT EXISTS minifedi_actors (
    actorid INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    preferredUsername TEXT,
    name TEXT,
    summary TEXT,
    actortype TEXT DEFAULT 'Person'
);

CREATE TABLE IF NOT EXISTS minifedi_fep_521a_publickeys (
    publickeyid INTEGER PRIMARY KEY AUTOINCREMENT,
    publickey TEXT,
    actor INTEGER,
    FOREIGN KEY (actor) REFERENCES minifedi_actors (actorid)
);

CREATE TABLE IF NOT EXISTS minifedi_inbox (
    inboxid INTEGER PRIMARY KEY AUTOINCREMENT,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    read BOOLEAN DEFAULT FALSE,
    actor INTEGER,
    FOREIGN KEY (actor) REFERENCES minifedi_actors (actorid)
);

CREATE TABLE IF NOT EXISTS minifedi_outbox (
    outboxid INTEGER PRIMARY KEY AUTOINCREMENT,
    message TEXT,
    processed BOOLEAN DEFAULT FALSE,
    read BOOLEAN DEFAULT FALSE,
    actor INTEGER,
    FOREIGN KEY (actor) REFERENCES minifedi_actors (actorid)
);
