<?php
/**
 * ============================================================
 * BackupService.php
 * Serviço responsável pelo backup do banco de dados.
 * 
 * FUNCIONALIDADES:
 * - Backup completo do banco de dados
 * - Backup de tabelas específicas
 * - Restauração de backups
 * - Agendamento de backups automáticos
 * - Limpeza de backups antigos
 * - Compactação de backups
 * 
 * @package EquaTEA
 * @subpackage Services
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// CARREGAMENTO DE DEPENDÊNCIAS
// ============================================================

require_once MODELS_PATH . '/../config/database.php';

/**
 * Class BackupService
 * 
 * Gerencia o backup e restauração do banco de dados MySQL,
 * com suporte a compactação, agendamento e limpeza automática.
 * 
 * @author Equipe EquaTEA
 */
class BackupService
{
    /**
     * @var object Conexão com o banco de dados
     */
    private $db;
    
    /**
     * @var string Nome do banco de dados
     */
    private $dbname;
    
    /**
     * @var string Caminho da pasta de backups
     */
    private $backup_dir;
    
    /**
     * @var string Usuário do banco de dados
     */
    private $db_user;
    
    /**
     * @var string Senha do banco de dados
     */
    private $db_password;
    
    /**
     * @var string Host do banco de dados
     */
    private $db_host;
    
    /**
     * @var array Configurações de backup
     */
    private $config;

    /**
     * Construtor da classe
     * 
     * @param array $config Configurações personalizadas (opcional)
     */
    public function __construct($config = [])
    {
        // ============================================================
        // 1. CARREGAR CONFIGURAÇÕES DO BANCO
        // ============================================================
        
        $this->db = Database::getInstance()->getConnection();
        
        // Tenta obter as configurações do banco
        $this->dbname = $config['dbname'] ?? $this->obterNomeBanco();
        $this->db_user = $config['db_user'] ?? $this->obterUsuarioBanco();
        $this->db_password = $config['db_password'] ?? $this->obterSenhaBanco();
        $this->db_host = $config['db_host'] ?? $this->obterHostBanco();
        
        // ============================================================
        // 2. PASTA DE BACKUPS
        // ============================================================
        
        $this->backup_dir = $config['backup_dir'] ?? dirname(__DIR__, 2) . '/backups/';
        
        // Cria a pasta se não existir
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
        
        // ============================================================
        // 3. CONFIGURAÇÕES PADRÃO
        // ============================================================
        
        $this->config = [
            'max_backups' => $config['max_backups'] ?? 30, // Máximo de backups mantidos
            'compress' => $config['compress'] ?? true, // Compressão com gzip
            'include_data' => $config['include_data'] ?? true, // Incluir dados
            'include_structure' => $config['include_structure'] ?? true, // Incluir estrutura
            'tables' => $config['tables'] ?? null, // Tabelas específicas (null = todas)
            'exclude_tables' => $config['exclude_tables'] ?? ['logs_sistema'], // Tabelas a excluir
            'add_drop_table' => $config['add_drop_table'] ?? true // Adicionar DROP TABLE
        ];
    }

    // ============================================================
    // MÉTODOS DE BACKUP
    // ============================================================

    /**
     * Cria um backup completo do banco de dados
     * 
     * @param bool $compress Se deve comprimir o arquivo
     * @param string $nome_personalizado Nome personalizado para o arquivo
     * @return array|false Informações do backup ou false em caso de erro
     */
    public function criarBackup($compress = null, $nome_personalizado = null)
    {
        try {
            // ============================================================
            // 1. PREPARAR NOME DO ARQUIVO
            // ============================================================
            
            $compress = $compress ?? $this->config['compress'];
            $timestamp = date('Y-m-d_H-i-s');
            $nome = $nome_personalizado ?? "backup_{$this->dbname}_{$timestamp}";
            $extensao = $compress ? '.sql.gz' : '.sql';
            $arquivo = $this->backup_dir . $nome . $extensao;
            
            // ============================================================
            // 2. OBTER AS TABELAS
            // ============================================================
            
            $tabelas = $this->obterTabelas();
            
            if (empty($tabelas)) {
                throw new Exception('Nenhuma tabela encontrada para backup.');
            }
            
            // ============================================================
            // 3. GERAR O CONTEÚDO DO BACKUP
            // ============================================================
            
            $conteudo = $this->gerarCabecalhoBackup();
            
            foreach ($tabelas as $tabela) {
                // Verifica se a tabela deve ser excluída
                if (in_array($tabela, $this->config['exclude_tables'])) {
                    continue;
                }
                
                $conteudo .= $this->gerarBackupTabela($tabela);
            }
            
            $conteudo .= "\n-- Fim do backup\n";
            
            // ============================================================
            // 4. SALVAR O ARQUIVO
            // ============================================================
            
            if ($compress) {
                // Compactar com gzip
                $fp = gzopen($arquivo, 'wb9');
                gzwrite($fp, $conteudo);
                gzclose($fp);
            } else {
                file_put_contents($arquivo, $conteudo);
            }
            
            // ============================================================
            // 5. LIMPAR BACKUPS ANTIGOS
            // ============================================================
            
            $this->limparBackupsAntigos();
            
            // ============================================================
            // 6. REGISTRAR LOG
            // ============================================================
            
            $tamanho = filesize($arquivo);
            $tamanho_formatado = $this->formatarTamanho($tamanho);
            
            $this->logBackup('BACKUP_CRIADO', "Backup criado: {$nome} ({$tamanho_formatado})");
            
            return [
                'arquivo' => $arquivo,
                'nome' => $nome,
                'tamanho' => $tamanho,
                'tamanho_formatado' => $tamanho_formatado,
                'tabelas' => count($tabelas),
                'data' => date('Y-m-d H:i:s'),
                'compress' => $compress
            ];
            
        } catch (Exception $e) {
            $this->logBackup('BACKUP_ERRO', "Erro ao criar backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gera o cabeçalho do arquivo de backup
     * 
     * @return string Cabeçalho do backup
     */
    private function gerarCabecalhoBackup()
    {
        $cabecalho = "-- ============================================================\n";
        $cabecalho .= "-- BACKUP DO BANCO DE DADOS - EquaTEA\n";
        $cabecalho .= "-- ============================================================\n";
        $cabecalho .= "-- Banco: " . $this->dbname . "\n";
        $cabecalho .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
        $cabecalho .= "-- Host: " . $this->db_host . "\n";
        $cabecalho .= "-- ============================================================\n\n";
        $cabecalho .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $cabecalho .= "SET AUTOCOMMIT = 0;\n";
        $cabecalho .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $cabecalho .= "SET NAMES utf8mb4;\n\n";
        
        return $cabecalho;
    }

    /**
     * Gera o backup de uma tabela específica
     * 
     * @param string $tabela Nome da tabela
     * @return string Conteúdo do backup da tabela
     */
    private function gerarBackupTabela($tabela)
    {
        $conteudo = "";
        
        // ============================================================
        // 1. ESTRUTURA DA TABELA
        // ============================================================
        
        if ($this->config['include_structure']) {
            // Buscar CREATE TABLE
            $sql = "SHOW CREATE TABLE `{$tabela}`";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                $create_table = $result['Create Table'];
                
                if ($this->config['add_drop_table']) {
                    $conteudo .= "\nDROP TABLE IF EXISTS `{$tabela}`;\n";
                }
                
                $conteudo .= $create_table . ";\n\n";
            }
        }
        
        // ============================================================
        // 2. DADOS DA TABELA
        // ============================================================
        
        if ($this->config['include_data']) {
            // Buscar dados
            $sql = "SELECT * FROM `{$tabela}`";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            if (!empty($rows)) {
                $conteudo .= "-- Dados da tabela: {$tabela}\n";
                $conteudo .= "INSERT INTO `{$tabela}` VALUES\n";
                
                $valores = [];
                foreach ($rows as $row) {
                    $valores_linha = [];
                    foreach ($row as $valor) {
                        if ($valor === null) {
                            $valores_linha[] = 'NULL';
                        } elseif (is_numeric($valor)) {
                            $valores_linha[] = $valor;
                        } else {
                            $valores_linha[] = "'" . addslashes($valor) . "'";
                        }
                    }
                    $valores[] = "(" . implode(", ", $valores_linha) . ")";
                }
                
                $conteudo .= implode(",\n", $valores) . ";\n\n";
            }
        }
        
        return $conteudo;
    }

    // ============================================================
    // MÉTODOS DE RESTAURAÇÃO
    // ============================================================

    /**
     * Restaura um backup
     * 
     * @param string $arquivo Caminho do arquivo de backup
     * @param bool $truncate Se deve truncar as tabelas antes de restaurar
     * @return bool True se restaurado com sucesso
     */
    public function restaurarBackup($arquivo, $truncate = false)
    {
        try {
            if (!file_exists($arquivo)) {
                throw new Exception('Arquivo de backup não encontrado.');
            }
            
            // ============================================================
            // 1. LER O ARQUIVO
            // ============================================================
            
            $conteudo = $this->lerArquivoBackup($arquivo);
            
            if ($conteudo === false) {
                throw new Exception('Erro ao ler o arquivo de backup.');
            }
            
            // ============================================================
            // 2. EXECUTAR AS QUERIES
            // ============================================================
            
            $this->db->beginTransaction();
            
            // Se truncate, remove os dados das tabelas
            if ($truncate) {
                $tabelas = $this->obterTabelas();
                foreach ($tabelas as $tabela) {
                    $this->db->prepare("TRUNCATE TABLE `{$tabela}`")->execute();
                }
            }
            
            // Executa as queries do backup
            $queries = $this->splitQueries($conteudo);
            $total = 0;
            
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $this->db->prepare($query)->execute();
                    $total++;
                }
            }
            
            $this->db->commit();
            
            $this->logBackup('BACKUP_RESTAURADO', "Backup restaurado: " . basename($arquivo) . " ({$total} queries)");
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logBackup('RESTAURAR_ERRO', "Erro ao restaurar backup: " . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // MÉTODOS DE GERENCIAMENTO DE BACKUPS
    // ============================================================

    /**
     * Lista todos os backups disponíveis
     * 
     * @param bool $ordenar_por_data Se deve ordenar por data
     * @return array Lista de backups
     */
    public function listarBackups($ordenar_por_data = true)
    {
        $backups = [];
        $arquivos = glob($this->backup_dir . '*.{sql,sql.gz}', GLOB_BRACE);
        
        foreach ($arquivos as $arquivo) {
            $nome = basename($arquivo);
            $tamanho = filesize($arquivo);
            $extensao = pathinfo($arquivo, PATHINFO_EXTENSION);
            
            $backups[] = [
                'nome' => $nome,
                'arquivo' => $arquivo,
                'tamanho' => $tamanho,
                'tamanho_formatado' => $this->formatarTamanho($tamanho),
                'data' => date('Y-m-d H:i:s', filemtime($arquivo)),
                'extensao' => $extensao,
                'compress' => $extensao === 'gz'
            ];
        }
        
        if ($ordenar_por_data) {
            usort($backups, function($a, $b) {
                return strtotime($b['data']) - strtotime($a['data']);
            });
        }
        
        return $backups;
    }

    /**
     * Remove backups antigos (mantém apenas os N mais recentes)
     * 
     * @param int $quantidade Quantidade de backups a manter
     * @return int Número de backups removidos
     */
    public function limparBackupsAntigos($quantidade = null)
    {
        $quantidade = $quantidade ?? $this->config['max_backups'];
        $removidos = 0;
        
        $backups = $this->listarBackups(true);
        
        if (count($backups) > $quantidade) {
            $para_remover = array_slice($backups, $quantidade);
            
            foreach ($para_remover as $backup) {
                if (unlink($backup['arquivo'])) {
                    $removidos++;
                }
            }
            
            if ($removidos > 0) {
                $this->logBackup('BACKUP_LIMPEZA', "Removidos {$removidos} backups antigos.");
            }
        }
        
        return $removidos;
    }

    /**
     * Remove um backup específico
     * 
     * @param string $nome Nome do arquivo
     * @return bool True se removido
     */
    public function removerBackup($nome)
    {
        $arquivo = $this->backup_dir . $nome;
        
        if (file_exists($arquivo) && unlink($arquivo)) {
            $this->logBackup('BACKUP_REMOVIDO', "Backup removido: {$nome}");
            return true;
        }
        
        return false;
    }

    /**
     * Obtém informações de um backup específico
     * 
     * @param string $nome Nome do arquivo
     * @return array|false Informações do backup
     */
    public function getInfoBackup($nome)
    {
        $arquivo = $this->backup_dir . $nome;
        
        if (!file_exists($arquivo)) {
            return false;
        }
        
        return [
            'nome' => $nome,
            'arquivo' => $arquivo,
            'tamanho' => filesize($arquivo),
            'tamanho_formatado' => $this->formatarTamanho(filesize($arquivo)),
            'data' => date('Y-m-d H:i:s', filemtime($arquivo)),
            'extensao' => pathinfo($arquivo, PATHINFO_EXTENSION),
            'compress' => pathinfo($arquivo, PATHINFO_EXTENSION) === 'gz'
        ];
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Obtém a lista de tabelas do banco de dados
     * 
     * @return array Lista de tabelas
     */
    private function obterTabelas()
    {
        $sql = "SHOW TABLES FROM `{$this->dbname}`";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        $tabelas = [];
        foreach ($result as $row) {
            $tabelas[] = reset($row);
        }
        
        return $tabelas;
    }

    /**
     * Obtém o nome do banco de dados da conexão
     * 
     * @return string Nome do banco
     */
    private function obterNomeBanco()
    {
        $sql = "SELECT DATABASE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Obtém o usuário do banco de dados
     * 
     * @return string Usuário
     */
    private function obterUsuarioBanco()
    {
        $sql = "SELECT USER()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return explode('@', $result)[0];
    }

    /**
     * Obtém o host do banco de dados
     * 
     * @return string Host
     */
    private function obterHostBanco()
    {
        $sql = "SELECT USER()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $parts = explode('@', $result);
        return isset($parts[1]) ? $parts[1] : 'localhost';
    }

    /**
     * Obtém a senha do banco de dados (da configuração)
     * 
     * @return string Senha
     */
    private function obterSenhaBanco()
    {
        // Tenta obter a senha da configuração do arquivo database.php
        try {
            $reflection = new ReflectionClass('Database');
            $property = $reflection->getProperty('password');
            $property->setAccessible(true);
            $db = Database::getInstance();
            return $property->getValue($db);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Lê um arquivo de backup (compactado ou não)
     * 
     * @param string $arquivo Caminho do arquivo
     * @return string|false Conteúdo do arquivo
     */
    private function lerArquivoBackup($arquivo)
    {
        $extensao = pathinfo($arquivo, PATHINFO_EXTENSION);
        
        if ($extensao === 'gz') {
            $fp = gzopen($arquivo, 'rb');
            $conteudo = '';
            while (!gzeof($fp)) {
                $conteudo .= gzread($fp, 4096);
            }
            gzclose($fp);
            return $conteudo;
        } else {
            return file_get_contents($arquivo);
        }
    }

    /**
     * Divide um arquivo SQL em queries individuais
     * 
     * @param string $conteudo Conteúdo do SQL
     * @return array Lista de queries
     */
    private function splitQueries($conteudo)
    {
        // Remove comentários
        $conteudo = preg_replace('/--.*$/m', '', $conteudo);
        $conteudo = preg_replace('/\/\*.*?\*\//s', '', $conteudo);
        
        // Divide por ;
        $queries = explode(';', $conteudo);
        
        // Remove queries vazias
        $queries = array_filter($queries, function($query) {
            return trim($query) !== '';
        });
        
        return $queries;
    }

    /**
     * Formata o tamanho de um arquivo
     * 
     * @param int $bytes Tamanho em bytes
     * @param int $decimals Casas decimais
     * @return string Tamanho formatado
     */
    private function formatarTamanho($bytes, $decimals = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $decimals) . ' ' . $units[$i];
    }

    /**
     * Registra ações de backup no log
     * 
     * @param string $acao Nome da ação
     * @param string $descricao Descrição da ação
     */
    private function logBackup($acao, $descricao)
    {
        try {
            $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address) 
                    VALUES (NULL, :acao, :descricao, :ip)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':acao' => 'BACKUP_' . $acao,
                ':descricao' => $descricao,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            // Erro silencioso
            error_log("Erro ao registrar log de backup: " . $e->getMessage());
        }
    }

    // ============================================================
    // MÉTODOS DE CONFIGURAÇÃO E UTILIDADE
    // ============================================================

    /**
     * Define a pasta de backups
     * 
     * @param string $dir Caminho da pasta
     * @return bool True se a pasta foi criada
     */
    public function setBackupDir($dir)
    {
        $this->backup_dir = rtrim($dir, '/') . '/';
        
        if (!is_dir($this->backup_dir)) {
            return mkdir($this->backup_dir, 0755, true);
        }
        
        return true;
    }

    /**
     * Obtém a pasta de backups
     * 
     * @return string Caminho da pasta
     */
    public function getBackupDir()
    {
        return $this->backup_dir;
    }

    /**
     * Atualiza as configurações
     * 
     * @param array $config Configurações
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Obtém as configurações atuais
     * 
     * @return array Configurações
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Cria um backup do sistema completo (inclui arquivos)
     * 
     * @param bool $compress Se deve comprimir
     * @return array|false Informações do backup
     */
    public function criarBackupCompleto($compress = true)
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $nome = "backup_completo_{$this->dbname}_{$timestamp}";
            
            // Backup do banco
            $backup_db = $this->criarBackup($compress, $nome);
            
            if (!$backup_db) {
                throw new Exception('Erro ao criar backup do banco de dados.');
            }
            
            // ============================================================
            // BACKUP DOS ARQUIVOS DE CONFIGURAÇÃO
            // ============================================================
            
            $arquivos_config = [
                'config/config.php',
                'config/database.php',
                '.htaccess'
            ];
            
            $conteudo_arquivos = "# Arquivos de configuração\n";
            $conteudo_arquivos .= "# ==============================\n\n";
            
            foreach ($arquivos_config as $arquivo) {
                $caminho = dirname(__DIR__, 2) . '/' . $arquivo;
                if (file_exists($caminho)) {
                    $conteudo_arquivos .= "# ---- " . basename($arquivo) . " ----\n";
                    $conteudo_arquivos .= file_get_contents($caminho);
                    $conteudo_arquivos .= "\n\n";
                }
            }
            
            // Salvar arquivos de configuração
            $arquivo_config = $this->backup_dir . 'config_' . $timestamp . '.txt';
            file_put_contents($arquivo_config, $conteudo_arquivos);
            
            return [
                'banco' => $backup_db,
                'config' => $arquivo_config,
                'nome' => $nome,
                'data' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->logBackup('BACKUP_COMPLETO_ERRO', "Erro ao criar backup completo: " . $e->getMessage());
            return false;
        }
    }
}