### SQL LLM
- Start llm service eg Ollama (https://ollama.com/) and set api url to match your environment 
- Change .env.example to .env and update the db credentials
- Update questions in src/AI.php
- Run the command below in the project root to test
```
php src/AI.php
```

### Requirements
- PHP 8+
- Mysql

## Ollama Docs
- https://github.com/ollama/ollama/blob/main/docs/api.md