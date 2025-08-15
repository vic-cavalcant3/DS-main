// Variável para armazenar as imagens selecionadas
let imagensSelecionadas = [];

// Elementos do DOM
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('upload-imagem');
const btnSelecionar = document.getElementById('btn-selecionar');
const previewContainer = document.getElementById('preview-container');
const formProduto = document.getElementById('form-produto');
const tabelaProdutos = document.getElementById('tabela-produtos');

// Eventos para upload de imagens
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    dropArea.classList.add('drag-active');
}

function unhighlight() {
    dropArea.classList.remove('drag-active');
}

dropArea.addEventListener('drop', handleDrop, false);
btnSelecionar.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', handleFiles);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles({ target: { files } });
}

function handleFiles(e) {
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            previewImage(file);
            imagensSelecionadas.push(file);
        }
    }
}

function previewImage(file) {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function() {
        const previewDiv = document.createElement('div');
        previewDiv.className = 'relative w-20 h-20';
        previewDiv.innerHTML = `
            <img src="${reader.result}" class="w-full h-full object-cover rounded" alt="Preview">
            <button class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                ×
            </button>
        `;
        previewDiv.querySelector('button').addEventListener('click', () => {
            previewDiv.remove();
            imagensSelecionadas = imagensSelecionadas.filter(f => f !== file);
        });
        previewContainer.appendChild(previewDiv);
    };
}

// Formulário de produto
formProduto.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Simulando upload das imagens (em produção, enviaria para um servidor)
    const imagensUrls = [];
    for (const imagem of imagensSelecionadas) {
        // Aqui você faria o upload real para um servidor ou Firebase Storage
        const url = URL.createObjectURL(imagem);
        imagensUrls.push(url);
    }
    
    const novoProduto = {
        id: Date.now().toString(),
        nome: document.getElementById('nome-produto').value,
        descricao: document.getElementById('descricao-produto').value,
        preco: parseFloat(document.getElementById('preco-produto').value),
        categoria: document.getElementById('categoria-produto').value,
        tags: document.getElementById('tags-produto').value.split(',').map(tag => tag.trim()),
        imagens: imagensUrls,
        dataCadastro: new Date().toISOString(),
        status: 'ativo'
    };
    
    // Simulando salvamento (em produção, faria uma requisição para o backend)
    let produtos = JSON.parse(localStorage.getItem('produtos') || [];
    produtos.push(novoProduto);
    localStorage.setItem('produtos', JSON.stringify(produtos));
    
    // Atualiza a tabela
    carregarProdutos();
    
    // Limpa o formulário
    formProduto.reset();
    previewContainer.innerHTML = '';
    imagensSelecionadas = [];
    
    alert('Produto cadastrado com sucesso!');
});

// Carrega produtos na tabela
function carregarProdutos() {
    const produtos = JSON.parse(localStorage.getItem('produtos')) || [];
    tabelaProdutos.innerHTML = '';
    
    produtos.forEach(produto => {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-gray-200 hover:bg-gray-50';
        tr.innerHTML = `
            <td class="py-3 px-4">${produto.id}</td>
            <td class="py-3 px-4">
                <div class="flex items-center">
                    <img src="${produto.imagens[0] || 'https://via.placeholder.com/50'}" 
                         alt="${produto.nome}" 
                         class="w-10 h-10 rounded mr-3">
                    <span>${produto.nome}</span>
                </div>
            </td>
            <td class="py-3 px-4">${produto.categoria}</td>
            <td class="py-3 px-4">R$ ${produto.preco.toFixed(2)}</td>
            <td class="py-3 px-4">
                <span class="px-2 py-1 rounded-full text-xs ${produto.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${produto.status}
                </span>
            </td>
            <td class="py-3 px-4">
                <button class="text-blue-500 hover:text-blue-700 mr-3" onclick="editarProduto('${produto.id}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-red-500 hover:text-red-700" onclick="excluirProduto('${produto.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tabelaProdutos.appendChild(tr);
    });
}

// Funções auxiliares
function editarProduto(id) {
    const produtos = JSON.parse(localStorage.getItem('produtos')) || [];
    const produto = produtos.find(p => p.id === id);
    if (produto) {
        // Preenche o formulário com os dados do produto
        document.getElementById('nome-produto').value = produto.nome;
        document.getElementById('descricao-produto').value = produto.descricao;
        document.getElementById('preco-produto').value = produto.preco;
        document.getElementById('categoria-produto').value = produto.categoria;
        document.getElementById('tags-produto').value = produto.tags.join(', ');
        
        // Scroll para o formulário
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function excluirProduto(id) {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        let produtos = JSON.parse(localStorage.getItem('produtos')) || [];
        produtos = produtos.filter(p => p.id !== id);
        localStorage.setItem('produtos', JSON.stringify(produtos));
        carregarProdutos();
    }
}

// Carrega os produtos quando a página é aberta
document.addEventListener('DOMContentLoaded', carregarProdutos);