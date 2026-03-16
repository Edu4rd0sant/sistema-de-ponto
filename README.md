<h1 align="center">⏰ Primus Point - Sistema de Gestão de Ponto</h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Badge" />
  <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Badge" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS Badge" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript Badge" />
</p>

<p align="center">
  Um sistema moderno, responsivo e seguro para gestão de ponto eletrônico, desenvolvido com foco na usabilidade tanto para o colaborador quanto para o departamento de Recursos Humanos.
</p>

---

## 🎯 Sobre o Projeto

O **Primus Point** foi projetado do zero adotando a arquitetura **MVC (Model-View-Controller)** para garantir um código limpo, modular e de fácil manutenção. O sistema elimina as antigas planilhas manuais e oferece um ambiente digital onde o colaborador registra sua jornada de trabalho e o Rh gerencia as informações em tempo real.

Destaques técnicos incluem o uso da API Fetch do JavaScript para operações assíncronas (eliminando reloads desnecessários de página), componentes modernos utilizando **Tailwind CSS**, e forte segurança com **Prepared Statements (PDO)** contra SQL Injections.

## ✨ Funcionalidades Principais

### 👥 Para o Colaborador (Painel do Funcionário)
* **Registro de Ponto Ágil:** Interface intuitiva para bater o ponto online.
* **Histórico Detalhado:** Visualização clara das horas trabalhadas, horas extras e débitos (Banco de Horas).
* **Portal de Solicitações:** Abertura de chamados direto para o RH (Atestados, Férias, Ajustes).
* **Notificações em Tempo Real:** Alertas instantâneos informando se a solicitação foi *Deferida* ou *Indeferida*.

### 🛡️ Para o RH (Painel Administrativo)
* **Dashboard Interativo:** Gráficos e cards resumidos do status operacional da empresa.
* **Gestão de Espelhos de Ponto:** Edição manual e auditoria dos registros de horários de cada colaborador.
* **Caixa de Solicitações Integrada:** Modais modernos para provar ou recusar chamados dos funcionários, podendo incluir notas e justificativas.
* **Controle de Usuários e Permissões:** Criação de novos perfis, gestão de senhas e hierarquia de acesso (Comum vs. Admin).

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP Nativo orientado a objetos (OOP) + Arquitetura MVC
- **Banco de Dados:** MySQL com integração via PDO (*PHP Data Objects*)
- **Frontend / UI:** HTML5, Tailwind CSS para estilização utilitária
- **Interatividade:** Vanilla JavaScript (ES6+), Fetch API, e Ícones Phosphor
- **Segurança:** Hashes de senha seguros (`password_hash`), Proteção CSRF básica e validações rigorosas.

## 🚀 Como Executar o Projeto

### Pré-requisitos
Ter instalado na máquina:
* [XAMPP](https://www.apachefriends.org/) ou similar (PHP 8.0+ e MySQL)
* Git

### Passos da Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/SEU-USUARIO/sistema-de-ponto.git
   cd sistema-de-ponto
   ```

2. **Configure o Banco de Dados**
   * Abra seu MySQL (via phpMyAdmin ou terminal).
   * Crie um banco de dados chamado `sistemaponto`.
   * Importe o script de estruturação localizado no arquivo fornecido de migração/setup (`database/database.sql`).
   
3. **Configure as Variáveis de Ambiente**
   * Ajuste as credenciais do banco no arquivo de conexão (por padrão localizado em `app/Config/Database.php` ou `config/database.php`).
   ```php
   <?php
   // Exemplo do Config
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'sistemaponto');
   ```

4. **Inicie o Servidor Embutido do PHP**
   * No terminal, aponte para a pasta `public/`:
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Acesse a Aplicação**
   * Abra no seu navegador: `http://localhost:8000`

## 💡 Portfólio & Contexto

Este repositório serve como vitrine das minhas habilidades como Desenvolvedor Full-Stack. O foco aqui foi pegar um problema corporativo do mundo real (Gestão de RH) e apresentar não apenas o "código que funciona", mas sim uma ótima **Experiência do Usuário (UX)** agregada a uma arquitetura limpa backend.

---
> Desenvolvido com dedicação por Eduardo. Connect in [LinkedIn](https://linkedin.com) ou entre em contato.