cd ../db
rm app.db
sqlite3 app.db < ./schema.sql
sqlite3 app.db < ./data.sql
cd ./../scripts