<?php
if (!function_exists('formatarTipoBatida')) {
    function formatarTipoBatida($tipo) {
        switch($tipo) {
            case 'entrada': return '<span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="ph ph-sign-in"></i> Entrada</span>';
            case 'saida_almoco': return '<span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-orange-500/10 text-orange-400 border border-orange-500/20"><i class="ph ph-coffee"></i> Saída para Almoço</span>';
            case 'retorno_almoco': return '<span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20"><i class="ph ph-arrow-u-down-left"></i> Retorno do Almoço</span>';
            case 'saida': return '<span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20"><i class="ph ph-sign-out"></i> Saída (Fim)</span>';
            default: return '<span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-md text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/20"><i class="ph ph-question"></i> Desconhecido</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Histórico - Primus Point</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
        <!-- ÁREA LATERAL (SIDEBAR) -->
        <?php include __DIR__ . '/../../../includes/sidebar_func.php'; ?>

        <main class="flex-1 lg:ml-64 flex flex-col min-h-screen relative overflow-hidden">
            <!-- HEADER -->
            <?php include __DIR__ . '/../../../includes/header_func.php'; ?>

            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col items-center">
                
                <div class="w-full max-w-5xl bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-2xl mt-4 relative overflow-hidden flex flex-col min-h-[500px]">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-700/50 pb-6">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 shrink-0 border border-blue-500/20 shadow-inner">
                            <i class="ph ph-clock-counter-clockwise text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white tracking-tight">Meu Espelho de Ponto</h2>
                            <p class="text-slate-400 text-sm mt-0.5">Histórico completo de batidas e registros de jornada</p>
                        </div>
                    </div>
                    
                    <?php if(isset($erro_db)): ?>
                        <div class="bg-red-500/10 border border-red-500/20 text-red-500 text-sm p-4 rounded-lg flex items-start gap-3 mb-6">
                            <i class="ph ph-warning-circle text-xl mt-0.5"></i>
                            <div><?php echo $erro_db; ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto flex-1 rounded-xl border border-slate-700/50 bg-slate-900/30">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/50 bg-slate-900/50">
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider w-1/3 text-left"><i class="ph ph-calendar-blank mr-1"></i> Data</th>
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider w-1/3 text-left"><i class="ph ph-clock mr-1"></i> Horário Registrado</th>
                                    <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider w-1/3 text-left"><i class="ph ph-tag mr-1"></i> Tipo de Batida</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <?php if(count($historico) > 0): ?>
                                    <?php foreach($historico as $registro): 
                                        $data_obj = new DateTime($registro['data_hora']);
                                        $data_formatada = $data_obj->format('d/m/Y');
                                        $hora_formatada = $data_obj->format('H:i');
                                    ?>
                                    <tr class="hover:bg-slate-700/20 transition-colors">
                                        <td class="py-4 px-6 text-sm text-slate-300 font-medium whitespace-nowrap">
                                            <?php echo $data_formatada; ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="font-mono text-sm bg-slate-900 px-3 py-1.5 rounded-lg text-slate-200 border border-slate-700 shadow-inner">
                                                <?php echo $hora_formatada; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php echo formatarTipoBatida($registro['tipo']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="py-20 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-500">
                                                <i class="ph ph-ghost text-5xl mb-4 opacity-50"></i>
                                                <p class="text-sm">Nenhum registro de ponto encontrado no seu histórico diário.</p>
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
</body>
</html>




