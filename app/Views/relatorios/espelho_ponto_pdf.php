<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Espelho de Ponto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #0056b3;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
        }
        .records-table {
            width: 100%;
            border-collapse: collapse;
        }
        .records-table th, .records-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .records-table th {
            background-color: #f4f4f4;
        }
        .selfie-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .no-records {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Espelho de Ponto Eletrônico</h1>
        <p>Mês/Ano: <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?>/<?= $ano ?></p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Funcionário:</strong> <?= htmlspecialchars($funcionario['nome']) ?></td>
            <td><strong>Email:</strong> <?= htmlspecialchars($funcionario['email'] ?? 'N/A') ?></td>
            <td><strong>Cargo:</strong> <?= htmlspecialchars($funcionario['cargo'] ?? 'N/A') ?></td>
        </tr>
    </table>

    <?php if (empty($registros)): ?>
        <p class="no-records">Nenhum registro encontrado para este período.</p>
    <?php else: ?>
        <table class="records-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Comprovante (Selfie)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $reg): 
                    $data = date('d/m/Y', strtotime($reg['data_hora']));
                    $hora = date('H:i', strtotime($reg['data_hora']));
                    $tipo = ucfirst(str_replace('_', ' ', $reg['tipo']));
                    
                    // Tratamento para a imagem (URL absoluta é geralmente necessária par o DOMPDF)
                    $fotoUrl = '';
                    if (!empty($reg['foto'])) {
                        // Idealmente usar path absoluto do disco em DomPDF local ou Base64 embedado para simplificar na geração do PDF.
                        // Aqui faremos um fallback para uma URL se o arquivo existir publicamente, ou o path físico.
                        // Para este exemplo em localhost, path do arquivo do disco:
                        $caminhoFisico = __DIR__ . '/../../../public/uploads/selfies/' . $reg['foto'];
                        if (file_exists($caminhoFisico)) {
                            // Converter para Base64 para garantir carregamento dentro do PDF
                            $tipoImg = pathinfo($caminhoFisico, PATHINFO_EXTENSION);
                            $dadosImg = file_get_contents($caminhoFisico);
                            $base64 = 'data:image/' . $tipoImg . ';base64,' . base64_encode($dadosImg);
                            $fotoUrl = $base64;
                        }
                    }
                ?>
                <tr>
                    <td><?= $data ?></td>
                    <td><?= $hora ?></td>
                    <td><?= htmlspecialchars($tipo) ?></td>
                    <td>
                        <?php if ($fotoUrl): ?>
                            <img src="<?= $fotoUrl ?>" class="selfie-img" alt="Selfie">
                        <?php else: ?>
                            <span>Sem Foto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>



