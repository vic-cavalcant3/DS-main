<?php
$page_title = "Contato | Flamma Index";
$current_page = "contato";
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
        
        .contact-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #ef4444;
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        .btn-primary {
            background: #ef4444;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

            .flamma-bg {
            background-color: #000;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flamma-bg text-white py-4 px-6 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="../../index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                <img src="../../client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
            </a>
        </div>
    </div>

    <!-- Contact -->
    <section class="py-16 bg-white" id="contato">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Entre em Contato</h2>
                <div class="w-20 h-1 bg-red-600 mx-auto"></div>
                <p class="text-lg text-gray-700 mt-4 max-w-3xl mx-auto">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris.
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div>
                    <form class="space-y-6">
                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                            <input type="text" id="nome" name="nome" 
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" 
                                placeholder="Seu nome completo" required>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" id="email" name="email" 
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" 
                                placeholder="seu@email.com" required>
                        </div>
                        
                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" 
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" 
                                placeholder="(11) 99999-9999">
                        </div>
                        
                        <div>
                            <label for="assunto" class="block text-sm font-medium text-gray-700 mb-1">Assunto</label>
                            <select id="assunto" name="assunto" 
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" required>
                                <option value="">Selecione um assunto</option>
                                <option value="duvida">Lorem ipsum dolor</option>
                                <option value="pedido">Consectetur adipiscing</option>
                                <option value="trocas">Vivamus lacinia</option>
                                <option value="sugestao">Sed auctor neque</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
                            <textarea id="mensagem" name="mensagem" rows="5" 
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" 
                                placeholder="Sua mensagem..." required></textarea>
                        </div>
                        
                        <button type="submit" 
                            class="btn-primary w-full py-3 px-6 text-white font-medium rounded-md shadow-md">
                            Enviar Mensagem
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div class="space-y-8">
                    <div class="contact-card bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-start mb-4">
                            <div class="bg-red-100 p-3 rounded-full mr-4">
                                <i class="fas fa-map-marker-alt text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Endereço</h3>
                                <p class="text-gray-700">Lorem Ipsum, 123<br>Dolor Sit Amet<br>São Paulo - SP<br>01234-567</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-card bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-start mb-4">
                            <div class="bg-red-100 p-3 rounded-full mr-4">
                                <i class="fas fa-phone-alt text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Telefone</h3>
                                <p class="text-gray-700">(11) 3456-7890</p>
                                <p class="text-gray-700">(11) 98765-4321 (WhatsApp)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-card bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-start mb-4">
                            <div class="bg-red-100 p-3 rounded-full mr-4">
                                <i class="fas fa-envelope text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">E-mail</h3>
                                <p class="text-gray-700">contato@flammaindex.com.br</p>
                                <p class="text-gray-700">sac@flammaindex.com.br</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-card bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-start">
                            <div class="bg-red-100 p-3 rounded-full mr-4">
                                <i class="fas fa-clock text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Horário de Atendimento</h3>
                                <p class="text-gray-700">Segunda a sexta: 9h às 18h</p>
                                <p class="text-gray-700">Sábado: 9h às 13h</p>
                                <p class="text-gray-700">Domingo: Fechado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-100" id="faq">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Perguntas Frequentes</h2>
                <div class="w-20 h-1 bg-red-600 mx-auto"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-start">
                        <i class="fas fa-question-circle text-red-600 mr-2 mt-1"></i>
                        Lorem ipsum dolor sit amet?
                    </h3>
                    <p class="text-gray-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. 
                        Vivamus hendrerit arcu sed erat molestie vehicula.
                    </p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-start">
                        <i class="fas fa-question-circle text-red-600 mr-2 mt-1"></i>
                        Consectetur adipiscing elit?
                    </h3>
                    <p class="text-gray-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. 
                        Vivamus hendrerit arcu sed erat molestie vehicula.
                    </p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-start">
                        <i class="fas fa-question-circle text-red-600 mr-2 mt-1"></i>
                        Vivamus lacinia odio vitae?
                    </h3>
                    <p class="text-gray-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. 
                        Vivamus hendrerit arcu sed erat molestie vehicula.
                    </p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-start">
                        <i class="fas fa-question-circle text-red-600 mr-2 mt-1"></i>
                        Sed auctor neque eu tellus?
                    </h3>
                    <p class="text-gray-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. 
                        Vivamus hendrerit arcu sed erat molestie vehicula.
                    </p>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-12 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold mb-4">JUNTE-SE À COMUNIDADE FLAMMA</h2>
            <p class="text-gray-300 mb-6 max-w-2xl mx-auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <div class="max-w-md mx-auto flex">
                <input type="email" placeholder="Seu melhor e-mail" class="px-4 py-3 rounded-l-md w-full text-gray-900 focus:outline-none">
                <button class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-r-md font-medium transition duration-300">
                    Assinar
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
<footer class="bg-black text-white pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">FLAMMA</h3>
                <p class="text-gray-400 text-sm">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
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

        // Máscara para telefone
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                
                if (value.length > 10) {
                    value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 6) {
                    value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/^(\d*)/, '($1');
                }
                
                e.target.value = value;
            });
        }

        // Validação do formulário
        const contactForm = document.querySelector('form');
        if (contactForm) {
            contactForm.addEventListener('submit', function (e) {
                e.preventDefault();
                
                // Validação básica
                let isValid = true;
                const inputs = contactForm.querySelectorAll('input[required], textarea[required], select[required]');
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });
                
                if (isValid) {
                    // Simulação de envio
                    alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
                    contactForm.reset();
                } else {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                }
            });
        }

        // Inicializar carrinho vazio para a página contato
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.textContent = '0';
        }
    </script>
</body>

</html>