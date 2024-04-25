# Projectaanvraag with Docker

## Prerequisite
- Install Docker Desktop
- appconfig: you'll have to clone [appconfig](https://github.com/cultuurnet/appconfig) in the same folder as where you will clone [projectaanvraag-silex](https://github.com/cultuurnet/projectaanvraag-silex)

## Configure

### Configuration setup
To get or update the configuration files, run the following command in the root of the project.
This command will also try to add `host.docker.internal` to your `/etc/hosts` file. This requires `sudo` privileges.
```
$ make config
```

## Start

### Docker

Start the docker containers with the following command. Make sure to execute this inside the root of the project so the `.env` can be used.
```
$ make up
```

### RabbitMQ

Create an exchange called `main_exchange_no_delay` on host `udb3-vagrant` by accessing the RabbitMQ management interface on http://host.docker.internal:15673/ 

To login on the rabbit mq interface you can use the following credentials:
- user: `vagrant`
- pw: `vagrant`

### Migrations & Composer packages

To install all composer packages & migrate the database, run the following command:
```
$ make init
```

### CI

To execute all CI tasks, run the following command:
```
$ make ci
```
