
# Run punbb on Resin (Quercus PHP)

- install MySQL (http://dev.mysql.com/downloads/mysql/

- download http://caucho.com/products/resin/download/gpl#download and unpack

- download driver for mysql https://dev.mysql.com/downloads/connector/j/ and unpack mysql-connector-java-*.jar to resin ./lib dir

- copy this repo to webapps/ROOT

- start from terminal
```
java -jar lib/resin.jar start
```

- open url http://localhost:8080/
