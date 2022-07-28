CREATE USER postgres;
CREATE USER smice_database;

GRANT smice TO postgres;
GRANT smice TO smice_database;

GRANT ALL PRIVILEGES ON DATABASE smice TO postgres;
GRANT ALL PRIVILEGES ON DATABASE smice TO smice_database;
