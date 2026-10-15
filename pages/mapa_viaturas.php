<?php
// pages/mapa_viaturas.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/middleware_auth.php';
require_once '../components/DAO/ViaturaDAO.php';

$viaturas_para_mapa = [];

try {
    $db = Conexao::getConexao();
    // Mantemos a query original que traz as viaturas operantes com coordenadas
    $sql = "SELECT 
                v.prefixo, v.numero, v.placa, v.modelo,
                u.nome as unidade_nome,
                m.nome as municipio_nome,
                u.latitude as lat,
                u.longitude as lng
            FROM viaturas v
            JOIN unidades u ON v.lotacao_atual_id = u.id
            JOIN municipios m ON u.municipio_id = m.id
            WHERE v.status_da_viatura = 'Operante'
              AND u.latitude IS NOT NULL 
              AND u.longitude IS NOT NULL";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $viaturas_para_mapa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro no Mapa: " . $e->getMessage());
}
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #map-container {
        position: relative;
        height: 650px;
        width: 100%;
        background-color: #eee;
    }

    #map {
        height: 100%;
        width: 100%;
        border-radius: 8px;
        z-index: 1;
    }

    /* Estilização da lista de viaturas dentro do Popup */
    .popup-vtr-list {
        max-height: 200px;
        overflow-y: auto;
        margin-top: 10px;
        padding-right: 5px;
    }

    .popup-vtr-item {
        border-bottom: 1px solid #eee;
        padding: 5px 0;
    }

    .popup-vtr-item:last-child {
        border-bottom: none;
    }

    .leaflet-popup-content-title {
        font-weight: bold;
        color: #4e73df;
        margin-bottom: 2px;
        border-bottom: 2px solid #4e73df;
        font-size: 1.1em;
    }
</style>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Mapa de Localização da Frota</h1>

    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Viaturas por Unidade (Coordenadas Reais)</h6>
                    <span class="badge bg-primary"><?= count($viaturas_para_mapa) ?> Viaturas no Mapa</span>
                </div>
                <div class="card-body p-0">
                    <div id="map-container">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function () {

    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        if (window.myMap) {
            window.myMap.off();
            window.myMap.remove();
        }

        window.myMap = L.map('map').setView([-15.6010, -56.0974], 6);

        const ruas = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
            attribution: '&copy; OpenStreetMap'
        }).addTo(window.myMap);

        const satelite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
        );

        L.control.layers({
            "Ruas": ruas,
            "Satélite": satelite
        }).addTo(window.myMap);

        const viaturasRaw = <?= json_encode($viaturas_para_mapa) ?>;

        if (viaturasRaw.length === 0) return;

        // --- LÓGICA DE AGRUPAMENTO ---
        const unidadesAgrupadas = {};

        viaturasRaw.forEach(vtr => {
            // Criamos uma chave única baseada na coordenada (Lat_Lng)
            const key = `${vtr.lat}_${vtr.lng}`;
            
            if (!unidadesAgrupadas[key]) {
                unidadesAgrupadas[key] = {
                    nome: vtr.unidade_nome,
                    municipio: vtr.municipio_nome,
                    lat: parseFloat(vtr.lat),
                    lng: parseFloat(vtr.lng),
                    viaturas: []
                };
            }
            unidadesAgrupadas[key].viaturas.push(vtr);
        });

        const markersGroup = [];

        // --- CRIAÇÃO DOS MARCADORES ---
        Object.values(unidadesAgrupadas).forEach(unidade => {
            
            const marker = L.marker([unidade.lat, unidade.lng]).addTo(window.myMap);
            markersGroup.push([unidade.lat, unidade.lng]);

            // Montagem do HTML do Popup com a lista de viaturas
            let vtrHtml = '';
            unidade.viaturas.forEach(v => {
                vtrHtml += `
                    <div class="popup-vtr-item">
                        <i class="fas fa-car-side text-secondary me-1"></i>
                        <strong>${v.prefixo}-${v.numero}</strong> <br>
                        <small class="text-muted">${v.modelo} | ${v.placa || 'S/P'}</small>
                    </div>
                `;
            });

            const popupHTML = `
                <div class="leaflet-popup-content-title">
                    ${unidade.nome}
                </div>
                <div class="text-muted small mb-2">
                    <i class="fas fa-map-marker-alt"></i> ${unidade.municipio}
                </div>
                <div class="fw-bold small text-dark">
                    Viaturas alocadas (${unidade.viaturas.length}):
                </div>
                <div class="popup-vtr-list">
                    ${vtrHtml}
                </div>
            `;

            marker.bindPopup(popupHTML);
        });

        if(markersGroup.length > 0){
            const bounds = L.latLngBounds(markersGroup);
            window.myMap.fitBounds(bounds,{ padding:[50,50] });
        }
    }

    if (document.readyState === 'complete') {
        initMap();
    } else {
        window.addEventListener('load', initMap);
    }

})();
</script>