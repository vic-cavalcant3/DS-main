document.addEventListener('DOMContentLoaded', function() {
    // Simulando busca dos produtos (na prática, buscaria do localStorage ou de um backend)
    const produtos = JSON.parse(localStorage.getItem('produtos')) || [];
    const container = document.getElementById('produtos-container');
    
    produtos.forEach(produto => {
        const produtoElement = document.createElement('div');
        produtoElement.className = 'bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-transform hover:-translate-y-1';
        produtoElement.innerHTML = `
            <div class="relative">
                <img src="${produto.imagens[0] || 'https://via.placeholder.com/300'}" 
                     alt="${produto.nome}" 
                     class="w-full h-64 object-cover">
                ${produto.status === 'novo' ? `
                    <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                        NOVO
                    </span>
                ` : ''}
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg mb-1">${produto.nome}</h3>
                <span class="text-gray-600 text-sm">${produto.categoria}</span>
                <div class="mt-4 flex justify-between items-center">
                    <span class="font-bold text-red-500">R$ ${produto.preco.toFixed(2)}</span>
                    <button class="bg-black text-white px-3 py-1 rounded hover:bg-red-600 transition">
                        <i class="fas fa-shopping-cart mr-1"></i> Comprar
                    </button>
                </div>
            </div>
        `;
        container.appendChild(produtoElement);
    });
});