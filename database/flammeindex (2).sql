-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/08/2025 às 02:53
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
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tamanho` enum('PP','P','M','G','GG','XG') NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens`
--

CREATE TABLE `imagens` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `url_imagem` varchar(255) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `cor` varchar(50) DEFAULT 'default'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imagens`
--

INSERT INTO `imagens` (`id`, `produto_id`, `url_imagem`, `ordem`, `cor`) VALUES
(0, 1, 'admin/src/uploads/68ab8709bceb7.jpeg', 1, 'default'),
(0, 1, 'admin/src/uploads/68ab8709bd2ee.jpeg', 2, 'default'),
(0, 2, 'admin/src/uploads/68ab89e0e09c3.jpeg', 1, 'default'),
(0, 2, 'admin/src/uploads/68ab89e0e0dd1.jpeg', 2, 'default'),
(0, 3, 'admin/src/uploads/68ab89ef4be38.jpeg', 1, 'default'),
(0, 3, 'admin/src/uploads/68ab89ef4c267.jpeg', 2, 'default'),
(0, 4, 'admin/src/uploads/68ab89ff8ffa6.jpeg', 1, 'default'),
(0, 4, 'admin/src/uploads/68ab89ff903ff.jpeg', 2, 'default'),
(0, 5, 'admin/src/uploads/68ab8a0f21e86.jpeg', 1, 'default'),
(0, 5, 'admin/src/uploads/68ab8a0f22298.jpeg', 2, 'default');

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
(1, NULL, NULL, 'Camisa naruto', '', 'preto,branco', 'admin/src/uploads/68ab8709bceb7.jpeg', NULL, 50.00, 85.00, 0, 0, 'Anime,Naruto', 'ativo', 1, '2025-08-24 18:41:29', '2025-08-24 18:41:29'),
(2, NULL, NULL, 'Camisa naruto 1', '', 'cinza', 'admin/src/uploads/68ab89e0e09c3.jpeg', NULL, 50.00, 85.00, 0, 0, 'Anime,Naruto', 'ativo', 1, '2025-08-24 18:53:36', '2025-08-24 18:53:36'),
(3, NULL, NULL, 'Camisa naruto 2', '', 'preto,branco', 'admin/src/uploads/68ab89ef4be38.jpeg', NULL, 50.00, 85.00, 0, 0, 'Anime,Naruto', 'ativo', 1, '2025-08-24 18:53:51', '2025-08-24 18:53:51'),
(4, NULL, NULL, 'Camisa naruto 3', '', 'preto,branco', 'admin/src/uploads/68ab89ff8ffa6.jpeg', NULL, 50.00, 85.00, 0, 0, 'Anime,Naruto', 'ativo', 1, '2025-08-24 18:54:07', '2025-08-24 18:54:07'),
(5, NULL, NULL, 'Camisa naruto 4', '', 'preto,branco', 'admin/src/uploads/68ab8a0f21e86.jpeg', NULL, 50.00, 85.00, 0, 0, 'Anime,Naruto', 'ativo', 1, '2025-08-24 18:54:23', '2025-08-24 18:54:23');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
