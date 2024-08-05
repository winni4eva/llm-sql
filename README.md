### SQL LLM
- Start llm service eg Ollama (https://ollama.com/) and set api url to match your environment 
- Rename .env.example to .env and update the db credentials
- Run command
```
composer install
```
- Update questions in src/AI.php
- Run the command below in the project root with a question you want a report for to test
```
php src/AI.php "Get all account types, with their user details and balances"
```

```
php src/AI.php "Get all user account transactions"
```

### Requirements
- PHP 8+
- Mysql

### Ollama Docs
- https://github.com/ollama/ollama/blob/main/docs/api.md
