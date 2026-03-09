// funcionario.js
document.addEventListener('DOMContentLoaded', () => {
    iniciarRelogio();
    carregarPontoHoje();

    const btnRegistrar = document.querySelector('.btn-register');
    if (btnRegistrar) {
        btnRegistrar.addEventListener('click', registrarPonto);
    }
});

function iniciarRelogio() {
    const clockDisplay = document.querySelector('.clock-display');
    const dateDisplay = document.querySelector('.date-display');
    
    if (!clockDisplay || !dateDisplay) return;

    const atualizar = () => {
        const agora = new Date();
        
        // Atualiza hora
        const horas = String(agora.getHours()).padStart(2, '0');
        const minutos = String(agora.getMinutes()).padStart(2, '0');
        clockDisplay.innerText = `${horas}:${minutos}`;
        
        // Atualiza data
        const dia = String(agora.getDate()).padStart(2, '0');
        const meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        const mes = meses[agora.getMonth()];
        const ano = agora.getFullYear();
        dateDisplay.innerText = `${dia} de ${mes} de ${ano}`;
    };

    atualizar();
    setInterval(atualizar, 1000); // Atualiza a cada segundo
}

async function carregarPontoHoje() {
    try {
        const response = await fetch('api_get_ponto_hoje.php');
        const data = await response.json();
        
        if (data.sucesso) {
            renderizarRegistros(data.data);
            atualizarStatusDoBotao(data.data.length);
        }
    } catch (e) {
        console.error("Erro ao carregar o ponto de hoje:", e);
    }
}

function renderizarRegistros(registros) {
    const listContainer = document.querySelector('.records-list');
    const lastRecordNotice = document.querySelector('.last-record-notice span');
    
    if (!listContainer || !lastRecordNotice) return;
    
    // Tabela de mapeamento para exibição bonita
    const mapTipos = {
        'entrada': 'Entrada',
        'saida_almoco': 'Saída p/ Almoço',
        'retorno_almoco': 'Retorno do Almoço',
        'saida': 'Saída (Fim do Expediente)'
    };

    if (registros.length === 0) {
        listContainer.innerHTML = `
            <li class="record-item" style="justify-content: center; color: var(--text-secondary);">
                Nenhum registro encontrado hoje.
            </li>
        `;
        lastRecordNotice.innerText = "Sem registros hoje";
        return;
    }

    let html = '';
    registros.forEach(r => {
        const label = mapTipos[r.tipo] || r.tipo;
        html += `
            <li class="record-item">
                <div class="record-info">
                    <div class="record-type">${label}</div>
                </div>
                <div class="record-time">${r.hora}</div>
            </li>
        `;
    });
    
    listContainer.innerHTML = html;

    // Atualiza o notice inferior
    const ultimo = registros[registros.length - 1];
    const ultimoLabel = mapTipos[ultimo.tipo] || ultimo.tipo;
    lastRecordNotice.innerHTML = `Último registro: <strong>${ultimoLabel} às ${ultimo.hora}</strong>`;
}

function atualizarStatusDoBotao(qtdRegistros) {
    const btn = document.querySelector('.btn-register');
    if (!btn) return;
    
    if (qtdRegistros >= 4) {
        btn.disabled = true;
        btn.innerText = "JORNADA CONCLUÍDA";
        btn.style.backgroundColor = 'var(--text-secondary)';
        btn.style.cursor = 'not-allowed';
    } else {
        btn.disabled = false;
        btn.innerText = "REGISTRAR PONTO AGORA";
        btn.style.backgroundColor = ''; // Volta ao original do css
        btn.style.cursor = 'pointer';
    }
}

async function registrarPonto() {
    const btn = document.querySelector('.btn-register');
    const originalText = btn.innerText;
    
    btn.disabled = true;
    btn.innerText = "REGISTRANDO...";

    try {
        const response = await fetch('actions/registrar_ponto.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
            // Sem body porque o endpoint detecta o id da sessão e descobre o próximo tipo
        });
        
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error(text);
            alert("Erro desconhecido do servidor.");
            btn.disabled = false;
            btn.innerText = originalText;
            return;
        }
        
        if (data.sucesso) {
            alert(data.mensagem);
            carregarPontoHoje(); // Recarrega a lista
        } else {
            alert("Erro: " + data.erro);
            btn.disabled = false;
            btn.innerText = originalText;
        }
    } catch (e) {
        console.error("Erro na requisição de bater ponto:", e);
        alert("Falha de conexão.");
        btn.disabled = false;
        btn.innerText = originalText;
    }
}
