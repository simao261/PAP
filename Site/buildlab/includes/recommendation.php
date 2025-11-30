<?php
// Sistema de Recomendação de Builds
function getRecommendedBuilds($usage_preference = null, $performance_level = null) {
    global $pdo;
    
    $sql = "SELECT * FROM builds WHERE 1=1";
    $params = [];
    
    if ($usage_preference) {
        $sql .= " AND usage_tags LIKE ?";
        $params[] = "%$usage_preference%";
    }
    
    if ($performance_level) {
        $sql .= " AND performance_level = ?";
        $params[] = $performance_level;
    }
    
    $sql .= " ORDER BY price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getBuildRecommendation($usage, $budget = null) {
    global $pdo;
    
    $recommendations = [];
    
    switch ($usage) {
        case 'gaming':
            $recommendations = getRecommendedBuilds('gaming', 'high');
            break;
        case 'work':
            $recommendations = getRecommendedBuilds('work', 'mid');
            break;
        case 'study':
            $recommendations = getRecommendedBuilds('study', 'entry');
            break;
        case 'professional':
            $recommendations = getRecommendedBuilds('professional', 'extreme');
            break;
        default:
            $recommendations = getRecommendedBuilds();
    }
    
    // Filtrar por orçamento se especificado
    if ($budget) {
        $recommendations = array_filter($recommendations, function($build) use ($budget) {
            return $build['price'] <= $budget;
        });
    }
    
    return $recommendations;
}

function getUsageTags() {
    return [
        'gaming' => 'Gaming',
        'work' => 'Trabalho',
        'study' => 'Estudo',
        'professional' => 'Profissional',
        'content-creation' => 'Criação de Conteúdo',
        'budget' => 'Orçamento'
    ];
}

function getPerformanceLevels() {
    return [
        'entry' => 'Entrada',
        'mid' => 'Intermédio', 
        'high' => 'Alto Desempenho',
        'extreme' => 'Extremo'
    ];
}
?>
