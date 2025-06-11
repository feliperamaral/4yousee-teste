
# Processador de Vídeos - Solução do Teste Técnico PHP

##  Descrição

Solução para o desafio técnico:  [README-challenge.md](README-challenge.md)


##  Decisões Técnicas

### Symfony + PHP 8.4
Optei pelo `Symfony` por ser um framework espetacular e popular, mas principalmente por conta do [API Platform](https://api-platform.com/). Esse bundle define uma estrutura comum e familiar, facilitando a assim a manutenibilidade, organização e a utilização dos padrões `PSR`.
Naturalmente o `Symfony` incentiva o uso de variáveis de ambiente, o ajuda evitar variáveis "hard code"
E escolhendo o `Symfony` como framework, o ORM não poderia ser outro além do `Doctrine`.

### PostgreSQL
Aqui poderia ser tanto o `MySQL` quanto `PostgreSQL` ou qualquer banco de dados.
Com a camada de abstração do `Doctrine`, é indiferente o banco de dados para esse "case", já que não é exigido nenhum recurso especial.
Eu não optei pelo SQLite, pois preferi algo que rodasse como um serviço, para simular melhor um ambiente com vários sistemas isolados (microserviço?!)

### MinIO (como substituto do S3)
O `MinIO` é o um caso parecido com o `PostgreSQL`, como ele é abstraído pelo `aws/aws-sdk-php`, especificamente o `Aws\S3\S3Client`, escolhi o `MinIO` por ser uma sugestão e pela sua fácil integração com o `Docker`

### RabbitMQ
O `RabbitMQ` é o primeiro nome que vem na minha cabeça quando se fala em filas.
Além da excelente performance, ele tem uma integração com Symfony tanto como o nativo Messenger, quanto o Bundle.
Particularmente eu escolhi o `rabbitmq-bundle`, pois ele facilita com algumas ações no `bin/console`

### Docker
Não consigo nem me imaginar escrevendo um documento dizendo:

> Instala o PHP versão X
> Instale a extensão Y usando o PELC
> Rode "composer install"
>  Agora torça

Então, com o Docker eu consigo ter um controle e garantia do que roda na minha máquina, na sua e em produção.

##  Infraestrutura e Organização

Com o Docker, consigo ter quase uma **Infrastructure as Code**, e tudo isso está descrito no `docker-compose.yml`
Temos os seguintes serviços:
	

- **`4yousee-api:`**
	 - É a aplicação PHP + Symfony em si. A imagem desse serviço é "buildada" usando o `Dockerfile` a partir da imagem `php:8.4-fpm-alpine`.
	 - No `Dockerfile` é instalado o `nginx` (para servir de proxy para o `PHP-FPM`) e o `ffmpeg`
	 - É adicionado outras dependências como `pcntl`,  `sockets`, `composer` e etc.
	 - Temos em especial a pasta `./linux`, nessa pasta tem uma estrutura de pastas com os arquivos de configuração do `NGINX`, `PHP_FPM` e o `start` do container
	 - `./linux/start/` , nessa pasta tem os scripts de inicialização dos containers. Tanto o container da aplicação PHP em si, quanto do `queue-consumer` 

- **`4yousee-queue-consumer:`**
- Esse serviço aproveita a mesma imagem do `4yousee-api` para criar um novo container, que por sua vez roda o serviço `rabbitmq:consumer`
- A forma que foi estruturado, possibilita ter serviços distintos podendo ser escalados de forma independente dependendo do volume de acesso e carga do servidor.
- Mas também, se necessário for, pode ser executado dentro do mesmo container do `4yousee-api`, com essa flexibilidade, podemos tomar decisões com base na demanda

- **`4yousee-database:`**
- Aqui temos o `Postgres` na versão 17.5, e está exposto na porta `5433` e pode ser acessado através de qualquer cliente de banco, ser interface gráfica ou terminal

- **`4yousee-storage:`**
- Aqui está o `MinIO` com a sua interface web na porta [http://localhost:9001](http://localhost:9001)

- **`4yousee-storage-init:`**
- Aqui é usado a imagem `minio/mc` para criar o bucket principal e permitir eu acesso remoto

- **`4yousee-queue:`**
- Aqui está configurado o servidor do `RabbitMQ` com sua interface exposta na porta:  [http://localhost:9002](http://localhost:9002)

### Dependência de execução
O serviço **`4yousee-api`**  depende que os serviços **`4yousee-database`**,  **`4yousee-storage-init`** e   **`4yousee-api`** estejam `healthy`, `completed successfully` e `started`  respectivamente.
E então, o **`4yousee-queue-consumer`** depende que o **`4yousee-api`** esteja  `healthy`

Quando o **`4yousee-queue-consumer`** estiver pronto, a aplicação está "toda rodando"

Para isso, foi configurado cada `healthcheck` de acordo com cada tipo de processo



##  Como Executar

### Pré-requisitos

- Docker na versão 28+

### Passo a Passo

```bash
# Clone o repositório
git clone https://github.com/feliperamaral/4yousee-teste.git
cd 4yousee-teste

# Suba os serviços
docker compose up
#### Aguarde o serviço "4yousee-consumer" exiba a mensagem ">>> Pronto <<<"
