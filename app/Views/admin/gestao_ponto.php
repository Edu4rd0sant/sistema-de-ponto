<?php
if (!function_exists('formatarTipoBatidaAdmin')) {
    function formatarTipoBatidaAdmin($tipo) {
        switch($tipo) {
            case 'entrada': return '<span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="ph ph-sign-in"></i> Entrada</span>';
            case 'saida_almoco': return '<span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-orange-500/10 text-orange-400 border border-orange-500/20"><i class="ph ph-coffee"></i> Saída Almoço</span>';
            case 'retorno_almoco': return '<span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20"><i class="ph ph-sign-in"></i> Retorno Almoço</span>';
            case 'saida': return '<span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20"><i class="ph ph-sign-out"></i> Saída</span>';
            default: return '<span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20"><i class="ph ph-question"></i> Desconhecido</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Ponto Global - Primus Point</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="/js/admin.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-200 font-sans min-h-screen">
    <div class="flex w-full">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

        <main class="flex-1 lg:ml-64 flex flex-col min-h-screen relative overflow-hidden">
            <!-- HEADER -->
            <?php include __DIR__ . '/../../../includes/header.php'; ?>
            
            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col">
                <div class="mb-6 flex justify-between items-center text-white">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Gestão de Ponto Organizacional</h1>
                        <p class="text-slate-400 text-sm mt-1">Acompanhamento em tempo real de registros da equipe</p>
                    </div>
                </div>

                <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl flex flex-col flex-1">
                    
                    <div class="p-6 border-b border-slate-700/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <i class="ph ph-clock-user text-blue-400 text-xl"></i> Espelho de Ponto Global
                        </h2>
                        
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <div class="relative w-full sm:w-64">
                                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" placeholder="Buscar na tabela..." 
                                    class="w-full bg-slate-900/50 border border-slate-700 rounded-lg pl-10 pr-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-slate-500">
                            </div>
                            <select class="bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors appearance-none cursor-pointer whitespace-nowrap hidden sm:block">
                                <option>Todos os Registros</option>
                                <option>Apenas Hoje</option>
                                <option>Essa Semana</option>
                            </select>
                            <button class="bg-slate-700 hover:bg-slate-600 border border-slate-600 text-white rounded-lg px-3 py-2 transition-colors sm:hidden">
                                <i class="ph ph-funnel"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if(isset($erro_db)): ?>
                        <div class="m-6 bg-red-500/10 border border-red-500/20 text-red-500 text-sm p-4 rounded-lg flex items-start gap-3">
                            <i class="ph ph-warning-circle text-xl mt-0.5"></i>
                            <div><?php echo $erro_db; ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/50 bg-slate-900/30">
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Colaborador</th>
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Data do Registro</th>
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Hora Analítica</th>
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Classificação (Tipo)</th>
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Evidência</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <?php if(count($historico_global) > 0): ?>
                                    <?php foreach($historico_global as $registro): 
                                        $data_obj = new DateTime($registro['data_hora']);
                                        $data_formatada = $data_obj->format('d/m/Y');
                                        $hora_formatada = $data_obj->format('H:i');
                                        $iniciais = strtoupper(substr($registro['nome'], 0, 2));
                                    ?>
                                    <tr class="hover:bg-slate-700/20 transition-colors">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-300 shrink-0 shadow-inner">
                                                    <?php echo $iniciais; ?>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-slate-100"><?php echo htmlspecialchars($registro['nome']); ?></span>
                                                    <span class="text-[11px] text-slate-500">ID: #<?php echo str_pad($registro['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="py-4 px-6 text-sm text-slate-300 font-medium">
                                            <div class="flex items-center gap-2">
                                                <i class="ph ph-calendar-blank text-slate-500"></i>
                                                <?php echo $data_formatada; ?>
                                            </div>
                                        </td>
                                        
                                        <td class="py-4 px-6">
                                            <span class="font-mono text-sm bg-slate-900 px-2 py-1 rounded text-slate-300 border border-slate-700/50">
                                                <?php echo $hora_formatada; ?>
                                            </span>
                                        </td>
                                        
                                        <td class="py-4 px-6">
                                            <?php echo formatarTipoBatidaAdmin($registro['tipo']); ?>
                                        </td>

                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                <?php if($registro['foto']): ?>
                                                    <button onclick="verEvidencia('<?php echo $registro['foto']; ?>', '<?php echo $registro['latitude']; ?>', '<?php echo $registro['longitude']; ?>')" 
                                                            class="p-2 bg-slate-700/50 hover:bg-blue-600/20 text-blue-400 border border-slate-600 rounded-lg transition-all"
                                                            title="Ver Selfie">
                                                        <i class="ph ph-camera text-lg"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="p-2 text-slate-600" title="Sem foto"><i class="ph ph-camera-slash text-lg"></i></span>
                                                <?php endif; ?>

                                                <?php if($registro['latitude'] && $registro['longitude']): ?>
                                                    <a href="https://www.google.com/maps?q=<?php echo $registro['latitude']; ?>,<?php echo $registro['longitude']; ?>" 
                                                       target="_blank"
                                                       class="p-2 bg-slate-700/50 hover:bg-emerald-600/20 text-emerald-400 border border-slate-600 rounded-lg transition-all"
                                                       title="Ver Localização">
                                                        <i class="ph ph-map-pin text-lg"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="flex flex-col items-center justify-center py-16 text-slate-500">
                                                <i class="ph ph-ghost text-5xl mb-4 opacity-50"></i>
                                                <h3 class="text-lg font-medium text-slate-300 mb-1">Nada por aqui</h3>
                                                <p class="text-sm">Nenhum ponto registrado na empresa até o momento.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL DE VISUALIZAÇÃO DE EVIDÊNCIA -->
    <div id="modalEvidencia" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-sm">
        <div class="bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ph ph-image"></i> Evidência de Registro
                </h3>
                <button onclick="fecharModalEvidencia()" class="text-slate-400 hover:text-white transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>
            <div class="p-4 bg-black flex items-center justify-center min-h-[300px]">
                <img id="img-evidencia" src="" alt="Selfie do Colaborador" class="max-w-full max-h-[70vh] rounded-lg">
            </div>
            <div class="p-6">
                <div id="container-mapa" class="hidden mb-4">
                    <a id="link-mapa" href="#" target="_blank" class="flex items-center justify-center gap-2 w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-medium transition-all">
                        <i class="ph ph-map-trifold text-lg"></i> VER LOCALIZAÇÃO NO MAPA
                    </a>
                </div>
                <button onclick="fecharModalEvidencia()" class="w-full bg-slate-700 hover:bg-slate-600 text-white py-2 rounded-lg text-sm transition-colors">
                    FECHAR
                </button>
            </div>
        </div>
    </div>

    <script>
        function verEvidencia(foto, lat, lng) {
            const modal = document.getElementById('modalEvidencia');
            const img = document.getElementById('img-evidencia');
            const containerMapa = document.getElementById('container-mapa');
            const linkMapa = document.getElementById('link-mapa');

            img.src = 'uploads/selfies/' + foto;
            
            if (lat && lng && lat !== 'null' && lng !== 'null') {
                containerMapa.classList.remove('hidden');
                linkMapa.href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
            } else {
                containerMapa.classList.add('hidden');
            }

            modal.classList.remove('hidden');
        }

        function fecharModalEvidencia() {
            document.getElementById('modalEvidencia').classList.add('hidden');
        }
    </script>
</body>
</html>




