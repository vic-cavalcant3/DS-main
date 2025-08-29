-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29/08/2025 às 21:54
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `flammeindex`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `admins`
--

INSERT INTO `admins` (`id`, `usuario`, `senha`, `nome`) VALUES
(1, 'admin', '$2y$10$Dua4QE6S8e/pUwR/E4S9XOFntg27v4ubvu3brE.hGuC9H87GlCXgi', 'Administrador');

-- --------------------------------------------------------

--
-- Estrutura para tabela `animes`
--

CREATE TABLE `animes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `slug`, `ativo`, `created_at`) VALUES
(1, 'Masculino', 'masculino', 1, '2025-08-25 18:19:30'),
(2, 'Feminino', 'feminino', 1, '2025-08-25 18:19:30'),
(3, 'Infantil', 'infantil', 1, '2025-08-25 18:19:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tamanho` enum('PP','P','M','G','GG','XG') NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id`, `produto_id`, `tamanho`, `quantidade`) VALUES
(0, 16, 'PP', 5),
(0, 16, 'P', 6),
(0, 16, 'M', 1),
(0, 16, 'G', 8),
(0, 16, 'GG', 10),
(0, 16, 'XG', 11);

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens`
--

CREATE TABLE `imagens` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `url_imagem` varchar(255) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `cor` varchar(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imagens`
--

INSERT INTO `imagens` (`id`, `produto_id`, `url_imagem`, `ordem`, `cor`) VALUES
(0, 16, 'admin/src/uploads/68b1d46f9df7e_preto.jpeg', 1, 'preto'),
(0, 16, 'admin/src/uploads/68b1d46f9e5d1_preto.jpeg', 2, 'preto'),
(0, 16, 'admin/src/uploads/68b1d46f9ea7e_preto.jpeg', 3, 'preto'),
(0, 16, 'admin/src/uploads/68b1d46f9ee96_branco.jpeg', 4, 'branco'),
(0, 16, 'admin/src/uploads/68b1d46f9f2f2_branco.jpeg', 5, 'branco'),
(0, 16, 'admin/src/uploads/68b1d46f9f792_branco.jpeg', 6, 'branco');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `anime_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cores` varchar(255) DEFAULT NULL,
  `imagem_principal` varchar(255) DEFAULT NULL,
  `imagem_hover` varchar(255) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_original` decimal(10,2) DEFAULT NULL,
  `desconto` int(11) DEFAULT 0,
  `vendas` int(11) DEFAULT 0,
  `tags` varchar(255) DEFAULT NULL,
  `status` enum('ativo','esgotado') DEFAULT 'ativo',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `categoria_id`, `anime_id`, `nome`, `descricao`, `cores`, `imagem_principal`, `imagem_hover`, `preco`, `preco_original`, `desconto`, `vendas`, `tags`, `status`, `ativo`, `created_at`, `data_cadastro`) VALUES
(16, 1, NULL, 'Camiseta Attack on Titan', 'Entre no universo épico de Attack on Titan com essa camiseta incrível!', 'preto,branco', 'admin/src/uploads/68b1d46f9df7e_preto.jpeg', NULL, 75.00, 80.00, 0, 0, 'Anime, Attack on Titan', 'ativo', 1, '2025-08-29 13:25:19', '2025-08-29 13:25:19');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
