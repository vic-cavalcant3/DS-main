<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte - Flamma</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
        }
        .flamma-bg {
            background-color: #000;
        }
        .flamma-text {
            color: #ef4444;
        }
        .page-section {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        .active-section {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .support-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active {
            background-color: #ef4444;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Simple Header -->
    <div class="flamma-bg text-white py-4 px-6 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="../../index.php" class="text-2xl font-bold text-gray-900 hover:opacity-90 transition-opacity">
                <img src="../../client/src/Flamma-logo.png" alt="Flamma Index" class="h-10 md:h-12 w-auto">
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Central de Suporte</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris.</p>
        </div>

        <!-- Tab Navigation -->
        <div class="flex flex-wrap justify-center gap-2 mb-12">
            <button class="tab-button px-6 py-3 rounded-full bg-gray-200 font-medium text-gray-700 active" data-tab="help">
                <i class="fas fa-question-circle mr-2"></i>Central de Ajuda
            </button>
            <button class="tab-button px-6 py-3 rounded-full bg-gray-200 font-medium text-gray-700" data-tab="exchanges">
                <i class="fas fa-exchange-alt mr-2"></i>Trocas e Devoluções
            </button>
            <button class="tab-button px-6 py-3 rounded-full bg-gray-200 font-medium text-gray-700" data-tab="delivery">
                <i class="fas fa-truck mr-2"></i>Entregas
            </button>
        </div>

        <!-- Help Section -->
        <div id="help" class="page-section active-section">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="support-card bg-white p-6 rounded-lg shadow">
                    <div class="text-red-500 text-2xl mb-4">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Dúvidas sobre Produtos</h3>
                    <p class="text-gray-600 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum.</p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor sit amet</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Consectetur adipiscing elit</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Sed do eiusmod tempor incididunt</span>
                        </li>
                    </ul>
                </div>

                <div class="support-card bg-white p-6 rounded-lg shadow">
                    <div class="text-red-500 text-2xl mb-4">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Dúvidas sobre Pagamento</h3>
                    <p class="text-gray-600 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum.</p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor sit amet</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Consectetur adipiscing elit</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Sed do eiusmod tempor incididunt</span>
                        </li>
                    </ul>
                </div>

                <div class="support-card bg-white p-6 rounded-lg shadow">
                    <div class="text-red-500 text-2xl mb-4">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Dúvidas sobre Conta</h3>
                    <p class="text-gray-600 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum.</p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Lorem ipsum dolor sit amet</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Consectetur adipiscing elit</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-red-500 mt-1 mr-2"></i>
                            <span>Sed do eiusmod tempor incididunt</span>
                        </li>
                    </ul>
                </div>

                <div class="support-card bg-white p-6 rounded-lg shadow">
                    <div class="text-red-500 text-2xl mb-4">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Fale Conosco</h3>
                    <p class="text-gray-600 mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum.</p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium">E-mail</h4>
                                <p class="text-gray-600">contato@flamma.com.br</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-phone text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium">Telefone</h4>
                                <p class="text-gray-600">(11) 3456-7890</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-clock text-red-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium">Horário de Atendimento</h4>
                                <p class="text-gray-600">Segunda a sexta, das 9h às 18h</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchanges Section -->
        <div id="exchanges" class="page-section">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Política de Trocas e Devoluções</h2>
                
                <div class="prose max-w-none">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Prazo para Solicitação</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. Vivamus hendrerit arcu sed erat molestie vehicula.</p>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Condições para Troca/Devolução</h3>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit</li>
                            <li>Nullam in dui mauris. Vivamus hendrerit arcu sed erat</li>
                            <li>Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor</li>
                            <li>Ut quis nulla ut quam interdum mollis</li>
                        </ul>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Processo de Troca</h3>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit</li>
                            <li>Nullam in dui mauris. Vivamus hendrerit arcu sed erat</li>
                            <li>Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor</li>
                            <li>Ut quis nulla ut quam interdum mollis</li>
                            <li>Maecenas ultricies mi eget mauris pharetra et ultrices</li>
                        </ol>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Reembolso</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris:</p>
                        <ul class="list-disc pl-5 space-y-2 mt-2">
                            <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit</li>
                            <li>Nullam in dui mauris. Vivamus hendrerit arcu sed erat</li>
                            <li>Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Section -->
        <div id="delivery" class="page-section">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Política de Entregas</h2>
                
                <div class="prose max-w-none">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Prazos de Entrega</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris:</p>
                        <ul class="list-disc pl-5 space-y-2 mt-2">
                            <li>Lorem ipsum dolor: 3 a 5 dias úteis</li>
                            <li>Consectetur adipiscing: 5 a 8 dias úteis</li>
                            <li>Nullam in dui mauris: 5 a 10 dias úteis</li>
                            <li>Vivamus hendrerit: 8 a 15 dias úteis</li>
                        </ul>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Formas de Envio</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="flex items-center p-3 border rounded-lg">
                                <div class="bg-gray-100 p-2 rounded-md mr-3">
                                    <i class="fas fa-truck text-red-500 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Lorem Ipsum</h4>
                                    <p class="text-sm text-gray-600">Sedex e PAC</p>
                                </div>
                            </div>
                            <div class="flex items-center p-3 border rounded-lg">
                                <div class="bg-gray-100 p-2 rounded-md mr-3">
                                    <i class="fas fa-shipping-fast text-red-500 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Dolor Sit</h4>
                                    <p class="text-sm text-gray-600">Entregas rápidas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Acompanhamento de Pedido</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. Vivamus hendrerit arcu sed erat molestie vehicula.</p>
                        <div class="bg-gray-100 p-4 rounded-lg mt-3">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-red-500 mr-2"></i>
                                <p class="text-sm">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-red-500">Frete Grátis</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Footer -->
    <div class="flamma-bg text-white py-6 px-6 mt-12">
        <div class="max-w-7xl mx-auto text-center">
            <p class="text-gray-400 text-sm">© 2025 Flamma. Todos os direitos reservados.</p>
        </div>
    </div>

    <script>
        // Tab switching functionality - CORRIGIDO
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const pageSections = document.querySelectorAll('.page-section');
            
            console.log("Script carregado. Botões encontrados:", tabButtons.length);
            console.log("Seções encontradas:", pageSections.length);

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    console.log("Clicou no botão:", tabId);
                    
                    // Update active tab button
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Show active section
                    pageSections.forEach(section => {
                        section.classList.remove('active-section');
                        if (section.id === tabId) {
                            console.log("Mostrando seção:", tabId);
                            section.classList.add('active-section');
                        }
                    });
                });
            });
            
            // Verificar se há um hash na URL para abrir uma seção específica
            if (window.location.hash) {
                const targetTab = window.location.hash.substring(1);
                console.log("Hash encontrado:", targetTab);
                
                const correspondingButton = document.querySelector(`.tab-button[data-tab="${targetTab}"]`);
                if (correspondingButton) {
                    console.log("Abrindo seção a partir do hash:", targetTab);
                    correspondingButton.click();
                }
            }
        });
    </script>
</body>
</html>