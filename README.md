# Tic Tac Toe game example powered by AI (using PHP-FANN)

## Usage:

```
docker build . -t v.matveyev/xogame-ai
docker run -v $(pwd):/app -it v.matveyev/xogame-ai:latest /bin/sh
composer install
```

### For help

```
php app.php
```

### To start playing with AI

```
php app.php play:ai 
```
