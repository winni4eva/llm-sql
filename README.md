### SQL LLM
- Start llm service eg Ollama (https://ollama.com/)
- Rename .env.example to .env and update the db credentials and llm service api url if different from the default
- Run command
```
composer install
```
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
