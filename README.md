# Project Setup

## Prerequisites

- PHP (>=8.3)
- Composer
- Npm vite
- MySQL or MariaDB
- Docker

### Copy the .env.example file

```
cp .env.example .env
```

### Run make start

```
make start
```

### Run make seed

```
make seed
```

### Run back

```
./vendor/bin/sail up -d
```

### Run front

```
./vendor/bin/sail npm run dev
```

