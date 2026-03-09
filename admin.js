// admin.js
async function checarNotificacoes() {
    try {
        const response = await fetch('api_get_solicitacoes.php');
        const data = await response.json();
        
        const badge = document.getElementById('badge-notificacoes');
        const badgeSidebar = document.getElementById('badge-sidebar-notificacoes');
        
        if (data.sucesso) {
            if (data.count > 0) {
                // Atualiza o badge do header
                if (badge) {
                    badge.innerText = data.count;
                    badge.style.display = 'flex';
                }
                
                // Atualiza o badge da sidebar
                if (badgeSidebar) {
                    badgeSidebar.innerText = data.count;
                    badgeSidebar.style.display = 'flex';
                }
                
                // Emite um alerta visual (mudança de cor no ícone do header)
                const bellIcon = document.querySelector('.notif-bell svg');
                if (bellIcon) {
                    bellIcon.style.stroke = 'var(--accent-red)';
                    setTimeout(() => {
                        bellIcon.style.stroke = 'var(--text-secondary)';
                    }, 1000);
                }
                
                // Atualiza a lista do modal se estiver aberto ou injeta os dados para quando abrir
                renderizarListaSolicitacoes(data.data);
            } else {
                if (badge) badge.style.display = 'none';
                if (badgeSidebar) badgeSidebar.style.display = 'none';
                renderizarListaSolicitacoes([]);
            }
        }
    } catch (error) {
        console.error('Erro ao checar notificações:', error);
    }
}

function renderizarListaSolicitacoes(solicitacoes) {
    const containerModal = document.getElementById('lista-solicitacoes-pendentes');
    const containerDash = document.getElementById('dashboard-lista-solicitacoes');
    
    if (solicitacoes.length === 0) {
        const msg = '<p style="text-align: center; color: var(--text-secondary); padding: 20px;">Não há solicitações pendentes no momento.</p>';
        if (containerModal) containerModal.innerHTML = msg;
        if (containerDash) containerDash.innerHTML = msg;
        return;
    }
    
    let html = '<ul class="notification-list">';
    solicitacoes.forEach(sol => {
        let icon = '📝';
        if (sol.tipo === 'ferias') icon = '🏖️';
        if (sol.tipo === 'ajuste_ponto') icon = '⏱️';
        if (sol.tipo === 'atestado') icon = '🏥';
        if (sol.tipo === 'banco_horas') icon = '⏳';
        
        const dataFormatada = new Date(sol.solicitada_em).toLocaleString('pt-BR');
        
        html += `
            <li style="display: flex; flex-direction: column; gap: 10px; align-items: flex-start; padding: 15px;">
                <div style="display: flex; gap: 10px; width: 100%;">
                    <div class="notif-icon notif-blue">${icon}</div>
                    <div class="notif-content" style="flex: 1;">
                        <div class="notif-text" style="font-size: 0.95rem;"><strong>${sol.nome_funcionario}</strong> - ${sol.tipo}</div>
                        <div class="notif-date">${dataFormatada}</div>
                        <div style="margin-top: 5px; color: var(--text-primary); font-size: 0.85rem;">
                            <em>"${sol.descricao}"</em>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-self: flex-end; margin-top: 10px;">
                    <button class="btn-action" style="color: var(--accent-green); border-color: var(--accent-green); padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="atualizarStatusSolicitacao(${sol.id}, 'aprovada')">✓ Aprovar</button>
                    <button class="btn-action" style="color: var(--accent-red); border-color: var(--accent-red); padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="atualizarStatusSolicitacao(${sol.id}, 'recusada')">✕ Recusar</button>
                </div>
            </li>
        `;
    });
    html += '</ul>';
    
    if (containerModal) containerModal.innerHTML = html;
    if (containerDash) containerDash.innerHTML = html;
}

// Prepare the function for future backend implementation
async function atualizarStatusSolicitacao(id, status) {
    try {
        const response = await fetch('api_atualizar_solicitacao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id, status: status })
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            alert(`Solicitação marcada como: ${status}!`);
            checarNotificacoes();
        } else {
            alert(`Erro: ${data.erro}`);
        }
    } catch (error) {
        console.error('Erro ao atualizar solicitação:', error);
        alert('Erro de conexão ao tentar atualizar a solicitação.');
    }
}

function abrirModalSolicitacoes() {
    const modal = document.getElementById('modalSolicitacoes');
    if (modal) {
        modal.classList.add('active');
        checarNotificacoes(); // Traz a versão mais recente ao abrir
    }
}

function fecharModalSolicitacoes() {
    const modal = document.getElementById('modalSolicitacoes');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Inicia o polling: checa a cada 10 segundos
document.addEventListener('DOMContentLoaded', () => {
    checarNotificacoes();
    setInterval(checarNotificacoes, 10000);
});
