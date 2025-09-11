# Sistema de Controle de Estoque - Pizzaria

Sistema completo de controle de estoque desenvolvido em PHP orientado a objetos para pizzarias, com controle de lotes, produção vinculada e rastreabilidade completa.

## 🚀 Funcionalidades

### ✅ Cadastro de Produtos
- Cadastro completo com preço de compra, peso/unidade e quantidade
- Interface intuitiva com validação de dados
- Controle de produtos ativos/inativos

### ✅ Produção Vinculada por Lotes
- Cada lote de produção está vinculado ao produto específico
- Cálculo automático de custo das porções baseado no preço do lote
- Se você comprar outro lote a preço diferente, o custo será ajustado automaticamente
- Sistema FIFO (First In, First Out) para controle de estoque

### ✅ Retirada para Pizzaria
- Sistema completo de registro de retiradas
- Baixa automática no estoque principal
- Histórico detalhado de todas as movimentações
- Controle por destino e responsável

### ✅ Relatórios Completos
- Relatório de entradas (lotes)
- Relatório de produção
- Relatório de retiradas
- Dashboard interativo com gráficos
- Relatório de estoque atual

## 🏗️ Arquitetura Técnica

- **PHP 8+ Orientado a Objetos**
- **Padrão MVC** adaptado
- **MySQL** com views otimizadas e triggers
- **Interface responsiva** com Bootstrap 5
- **JavaScript interativo** para melhor experiência
- **Sistema de debug** integrado

## 📋 Requisitos

- PHP 8.0 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL

## 🚀 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/estoque.git
cd estoque
```

### 2. Configure o banco de dados
- Edite o arquivo `config/config.php` com suas credenciais do MySQL
- Execute o script SQL: `sql/create_tables.sql`

### 3. Teste a instalação
- Acesse `teste_corrigido.php` no navegador
- Verifique se todos os testes passam

## 📁 Estrutura do Projeto

```
estoque/
├── config/
│   └── config.php              # Configurações do sistema
├── classes/
│   ├── Database.php            # Classe de conexão (Singleton)
│   ├── Produto.php             # Classe de produtos
│   ├── Lote.php                # Classe de lotes
│   ├── Producao.php            # Classe de produção
│   ├── Retirada.php            # Classe de retiradas
│   └── Relatorio.php           # Classe de relatórios
├── pages/
│   ├── produtos/               # Páginas de produtos
│   ├── lotes/                  # Páginas de lotes
│   ├── producao/               # Páginas de produção
│   ├── retiradas/              # Páginas de retiradas
│   └── relatorios/             # Páginas de relatórios
├── assets/
│   ├── css/                    # Estilos CSS
│   ├── js/                     # Scripts JavaScript
│   └── images/                 # Imagens
├── sql/
│   └── create_tables.sql       # Script de criação do banco
└── teste_corrigido.php         # Arquivo de testes
```

## 💡 Principais Diferenciais

### 🎯 Controle de Custo por Lote
Cada produção mantém o custo do lote original, permitindo rastreabilidade completa dos custos.

### 📊 Rastreabilidade Completa
Desde a compra até a retirada, todo movimento é rastreado e pode ser auditado.

### 🔄 Sistema FIFO
Primeiro que entra, primeiro que sai - garante rotação adequada do estoque.

### 📈 Dashboard Interativo
Interface moderna com gráficos e indicadores em tempo real.

### 🛡️ Validações Robustas
Sistema completo de validações para garantir integridade dos dados.

## 🔧 Como Usar

### 1. Cadastrar Produtos
- Acesse "Produtos" → "Cadastrar"
- Informe nome e unidade de medida
- Produto ficará ativo automaticamente

### 2. Registrar Lotes de Compra
- Acesse "Lotes" → "Cadastrar"
- Selecione o produto
- Informe preço de compra e quantidade
- Sistema calcula automaticamente o custo por unidade

### 3. Fazer Produções
- Acesse "Produção" → "Cadastrar"
- Selecione o lote (apenas lotes com estoque aparecem)
- Informe quantidade produzida e matéria-prima usada
- Sistema calcula custos automaticamente

### 4. Registrar Retiradas
- Acesse "Retiradas" → "Cadastrar"
- Selecione a produção (apenas com estoque disponível)
- Informe quantidade, destino e responsável
- Sistema dá baixa automaticamente

### 5. Consultar Relatórios
- Dashboard: visão geral do sistema
- Relatórios específicos por período
- Estoque atual detalhado

## 🐛 Resolução de Problemas

### Erro de Conexão com Banco
- Verifique as credenciais em `config/config.php`
- Certifique-se que o MySQL está rodando
- Verifique se o banco foi criado

### Erro de Propriedades Dinâmicas (PHP 8+)
- Todas as propriedades foram declaradas corretamente
- Sistema compatível com PHP 8+

### Problemas de Permissão
- Verifique permissões de escrita nos diretórios
- Configure adequadamente o servidor web

## 📝 Changelog

### v1.0.0 - Sistema Corrigido
- ✅ Corrigidos todos os erros de propriedades dinâmicas
- ✅ Corrigidos problemas de validação de dados
- ✅ Implementado sistema completo de classes
- ✅ Adicionadas todas as páginas de listagem
- ✅ Criados relatórios completos
- ✅ Sistema de debug implementado
- ✅ Compatibilidade com PHP 8+
- ✅ Documentação completa

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 👨‍💻 Autor

Sistema desenvolvido para controle de estoque de pizzaria com foco em rastreabilidade e controle de custos.

---

**Sistema 100% funcional e testado!** 🎉

