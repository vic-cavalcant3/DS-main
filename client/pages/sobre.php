<?php
$page_title = "Sobre | Flamma Index";
$current_page = "sobre";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }
        
        .font-bangers {
            font-family: 'Bangers', cursive;
            letter-spacing: 1px;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link:hover::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #ef4444;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .header-hero-connector {
            position: relative;
            height: 30px;
            width: 100%;
            background: linear-gradient(to bottom, 
                rgba(0, 0, 0, 1) 0%, 
                rgba(0, 0, 0, 0.8) 40%,
                rgba(0, 0, 0, 0.4) 70%,
                rgba(0, 0, 0, 0) 100%);
            z-index: 5;
            margin-top: -1px;
        }
        
        .hero-section {
            margin-top: -40px;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 3rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ef4444;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1.5rem;
            width: 2px;
            height: calc(100% + 1rem);
            background: #e5e7eb;
        }
        
        .timeline-item:last-child::after {
            display: none;
        }
        
        .team-member {
            transition: transform 0.3s ease;
        }
        
        .team-member:hover {
            transform: translateY(-5px);
        }
        
        .value-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #ef4444;
        }
        
        /* Estilo para a imagem da história */
        .history-image {
            max-height: 400px;
            object-fit: cover;
            width: 100%;
        }
        
        /* Ajustes para as imagens da equipe */
        .team-image-container {
            width: 160px;
            height: 160px;
        }
        
        @media (max-width: 640px) {
            .team-image-container {
                width: 120px;
                height: 120px;
            }
        }

                .flamma-bg {
            background-color: #000;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Top Announcement Bar -->
    <!-- <div class="bg-red-600 text-white text-center py-2 px-4 text-sm font-medium">
        FRETE GRÁTIS PARA COMPRAS ACIMA DE R$150 | CUPOM: FLAMMA10
    </div> -->

   <div class="flamma-bg text-white py-4 px-6 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="../../index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                <img src="../../client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
            </a>
        </div>
    </div>



    <!-- Nossa História -->
    <section class="py-16 bg-white" id="nossa-historia">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Nossa História</h2>
                <div class="w-20 h-1 bg-red-600 mx-auto"></div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <img src="https://images.unsplash.com/photo-1562157873-818bc0726f68?ixlib=rb-4.0.3&auto=format&fit=crop&w=654&q=80" 
     alt="Nossa história" 
     class="rounded-lg shadow-lg w-[500px] h-[600px]">

                </div>
                
                <div>
                    <p class="text-lg text-gray-700 mb-6">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
                    </p>
                    
                    <p class="text-lg text-gray-700 mb-6">
                        popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                    </p>
                    
                    <p class="text-lg text-gray-700">
                There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable.
                    </p>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="mt-20">
                <h3 class="text-2xl font-bold text-center text-gray-900 mb-10">Nossa Jornada</h3>
                <div class="max-w-3xl mx-auto">
                    <div class="timeline-item">
                        <h4 class="text-xl font-bold text-gray-900 mb-2">2020 - Fundação</h4>
                        <p class="text-gray-700"> Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h4 class="text-xl font-bold text-gray-900 mb-2">2021 - Primeira Coleção</h4>
                        <p class="text-gray-700">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h4 class="text-xl font-bold text-gray-900 mb-2">2022 - Expansão</h4>
                        <p class="text-gray-700">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h4 class="text-xl font-bold text-gray-900 mb-2">2023 - Reconhecimento</h4>
                        <p class="text-gray-700">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h4 class="text-xl font-bold text-gray-900 mb-2">2024 - Inovação</h4>
                        <p class="text-gray-700">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h4 class="text-xl font-bold text-gray-900 mb-2">2025 - Futuro</h4>
                        <p class="text-gray-700">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Missão, Visão e Valores -->
    <section class="py-16 bg-gray-100" id="valores">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Missão, Visão e Valores</h2>
                <div class="w-20 h-1 bg-red-600 mx-auto"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="value-card bg-white p-8 rounded-lg text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bullseye text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Missão</h3>
                    <p class="text-gray-700">
                        CLorem ipsum dolor sit amet, consectetur adipiscing elit. 
  Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
  Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                    </p>
                </div>
                
                <div class="value-card bg-white p-8 rounded-lg text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-eye text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Visão</h3>
                    <p class="text-gray-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
  Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
  Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                    </p>
                </div>
                
                <div class="value-card bg-white p-8 rounded-lg text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-heart text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Valores</h3>
                    <ul class="text-gray-700 text-left space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-600 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-600 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-600 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-600 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-600 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-black text-white pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">FLAMMA</h3>
                <p class="text-gray-400 text-sm">Camisetas premium de anime para verdadeiros fãs.</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">PRODUTOS</h3>
                <ul class="space-y-2">
                    <li><a href="../pages/masculino.php" class="text-gray-400 hover:text-red-500 text-sm">Masculino</a></li>
                    <li><a href="../pages/feminino.php" class="text-gray-400 hover:text-red-500 text-sm">Feminino</a></li>
                    <li><a href="../pages/infantil.php" class="text-gray-400 hover:text-red-500 text-sm">Infantil</a></li>
                    <li><a href="../pages/produtos.php" class="text-gray-400 hover:text-red-500 text-sm">Todos os Produtos</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">SUPORTE</h3>
                <ul class="space-y-2">
                    <li><a href="../pages/ajuda.php" class="text-gray-400 hover:text-red-500 text-sm">Central de Ajuda</a></li>
                    <li><a href="../pages/trocas.php" class="text-gray-400 hover:text-red-500 text-sm">Trocas e Devoluções</a></li>
                    <li><a href="../pages/entregas.php" class="text-gray-400 hover:text-red-500 text-sm">Entregas</a></li>
                    <li><a href="../pages/contato.php" class="text-gray-400 hover:text-red-500 text-sm">Fale Conosco</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">REDES SOCIAIS</h3>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-instagram text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-twitter text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500"><i class="fab fa-tiktok text-lg"></i></a>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-6 text-center">
            <p class="text-gray-400 text-sm">© 2025 Flamma. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Cart functionality
        const cartButton = document.getElementById('cart-button');
        const cartCount = document.getElementById('cart-count');

        // Inicializar carrinho vazio para a página sobre
        let cart = [];
        cartCount.textContent = '0';
    </script>
</body>

</html>