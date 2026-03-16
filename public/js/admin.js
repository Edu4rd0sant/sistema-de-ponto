// admin.js
async function checarNotificacoes() {
    try {
        const response = await fetch('/api/solicitacoes');
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
                    <button class="btn-action" style="color: var(--accent-red); border-color: var(--accent-red); padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="abrirModalRecusa(${sol.id})">✕ Recusar</button>
                </div>
            </li>
        `;
    });
    html += '</ul>';
    
    if (containerModal) containerModal.innerHTML = html;
    if (containerDash) containerDash.innerHTML = html;
}

// Prepare the function for future backend implementation
async function atualizarStatusSolicitacao(id, status, motivoRecusa = null) {
    try {
        const response = await fetch('/api/solicitacoes/atualizar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id, status: status, motivo_recusa: motivoRecusa })
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            if (status === 'aprovada') {
                showToast('Solicitação aprovada com sucesso!', 'success');
            } else {
                showToast('Solicitação recusada!', 'error');
            }
            checarNotificacoes();
        } else {
            showToast(`Erro: ${data.erro}`, 'error');
        }
    } catch (error) {
        console.error('Erro ao atualizar solicitação:', error);
        showToast('Erro de conexão ao tentar atualizar a solicitação.', 'error');
    }
}

function showToast(message, type) {
    // Remove toast anterior se houver
    const oldToast = document.getElementById('primus-toast');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'primus-toast';
    
    const colors = {
        success: 'bg-emerald-600',
        error: 'bg-red-600',
        info: 'bg-blue-600'
    };

    toast.className = `fixed bottom-10 left-1/2 -translate-x-1/2 ${colors[type] || 'bg-slate-800'} text-white px-6 py-3 rounded-full shadow-2xl z-50 flex items-center gap-3 animate-bounce`;
    
    let icon = 'ph-info';
    if (type === 'success') icon = 'ph-check-circle';
    if (type === 'error') icon = 'ph-warning-circle';

    toast.innerHTML = `<i class="ph ${icon} text-xl"></i> <span class="font-medium">${message}</span>`;
    
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('animate-bounce');
        toast.classList.add('transition-opacity', 'duration-500', 'opacity-0');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
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

function abrirModalRecusa(id) {
    document.getElementById('recusa_solicitacao_id').value = id;
    document.getElementById('recusa_motivo').value = '';
    
    const modalPrincipal = document.getElementById('modalSolicitacoes');
    if (modalPrincipal && modalPrincipal.classList.contains('active')) {
        modalPrincipal.classList.remove('active');
    }

    const modalRecusa = document.getElementById('modalRecusa');
    if (modalRecusa) {
        modalRecusa.classList.remove('hidden');
        modalRecusa.classList.add('flex', 'active');
        setTimeout(() => document.getElementById('recusa_motivo').focus(), 50);
    }
}

function fecharModalRecusa() {
    const modalRecusa = document.getElementById('modalRecusa');
    if (modalRecusa) {
        modalRecusa.classList.add('hidden');
        modalRecusa.classList.remove('flex', 'active');
    }
    
    const modalPrincipal = document.getElementById('modalSolicitacoes');
    if (modalPrincipal) modalPrincipal.classList.add('active');
}

function confirmarRecusa() {
    const id = document.getElementById('recusa_solicitacao_id').value;
    const motivo = document.getElementById('recusa_motivo').value;

    if (!motivo || motivo.trim() === '') {
        showToast('O motivo da recusa é obrigatório.', 'error');
        return;
    }

    atualizarStatusSolicitacao(id, 'recusada', motivo);
    fecharModalRecusa();
}

// Inicia o polling: checa a cada 10 segundos
document.addEventListener('DOMContentLoaded', () => {
    checarNotificacoes();
    setInterval(checarNotificacoes, 10000);
});
