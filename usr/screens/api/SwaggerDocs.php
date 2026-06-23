<?php

namespace Screens\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Screens API",
    version: "1.0.0",
    description: "API para la app móvil de Android compartiendo la sesión web."
)]
#[OA\SecurityScheme(
    securityScheme: "cookieAuth",
    type: "apiKey",
    in: "cookie",
    name: "PHPSESSID"
)]
#[OA\Server(url: "/", description: "Servidor principal")]
class SwaggerInfo {}

#[OA\Post(
    path: "/api/screens",
    summary: "Operaciones principales (Clientes, Órdenes)",
    description: "Maneja acciones C (crear), U (actualizar), D (eliminar) según los parámetros POST 'action' y 'part'. Para Consultas (GET) enviar action vacío y part correspondiente.",
    security: [["cookieAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(properties: [
                new OA\Property(property: "action",        type: "string",  description: "Acción: 'U' (Guardar), 'D' (Eliminar). Vacío para listar.", example: "U"),
                new OA\Property(property: "part",          type: "string",  description: "Módulo: 'CL' (Cliente), 'OR' (Orden), 'ST' (Estado), 'FI' (Firma), 'OP' (Parte Orden)", example: "CL"),
                new OA\Property(property: "clientId",      type: "integer", description: "ID de cliente (0 para nuevo)", example: 0),
                new OA\Property(property: "nombre",        type: "string",  description: "Nombre del cliente (para part=CL)"),
                new OA\Property(property: "telefono",      type: "string",  description: "Teléfono del cliente (para part=CL)"),
                new OA\Property(property: "ubicacion",     type: "string",  description: "Ubicación (para part=CL)"),
                new OA\Property(property: "latitud",       type: "number",  format: "float", description: "Latitud (para part=CL)"),
                new OA\Property(property: "longitud",      type: "number",  format: "float", description: "Longitud (para part=CL)"),
                new OA\Property(property: "orderId",       type: "integer", description: "ID de orden (para part=OR, ST, FI, OP)"),
                new OA\Property(property: "brandId",       type: "integer", description: "ID de marca (para part=OR)"),
                new OA\Property(property: "modelId",       type: "integer", description: "ID de modelo (para part=OR)"),
                new OA\Property(property: "modeloLibre",   type: "string",  description: "Modelo libre (para part=OR)"),
                new OA\Property(property: "pantallaLibre", type: "string",  description: "Pantalla libre (para part=OR)"),
                new OA\Property(property: "fallaReportada", type: "string",  description: "Falla reportada (para part=OR)"),
                new OA\Property(property: "costoEstimado", type: "number",  description: "Costo estimado (para part=OR)"),
                new OA\Property(property: "abonoInicial",  type: "number",  description: "Abono inicial (para part=OR)"),
                new OA\Property(property: "estado",        type: "string",  description: "Estado de la orden (para part=OR, ST)"),
                new OA\Property(property: "tipoPago",      type: "string",  description: "Tipo de pago (para part=OR, ST)"),
                new OA\Property(property: "notas",         type: "string",  description: "Notas (para part=OR)"),
                new OA\Property(property: "firmaBase64",   type: "string",  description: "Firma en base64 (para part=FI)"),
                new OA\Property(property: "orderPartId",   type: "integer", description: "ID de parte en la orden (para part=OP)"),
                new OA\Property(property: "partId",        type: "integer", description: "ID de la parte/repuesto (para part=OP)"),
                new OA\Property(property: "cantidad",      type: "integer", description: "Cantidad (para part=OP)"),
                new OA\Property(property: "precioUnit",    type: "number",  description: "Precio unitario (para part=OP)"),
            ])
        )
    ),
    responses: [new OA\Response(response: 200, description: "Respuesta JSON del sistema")]
)]
class ScreensEndpoint {}

#[OA\Post(
    path: "/api/screens/catalogs",
    summary: "Operaciones de Catálogos (Marcas, Modelos, Repuestos)",
    description: "Maneja creación, actualización y borrado. Para Consultas (GET) enviar action vacío.",
    security: [["cookieAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(properties: [
                new OA\Property(property: "action",      type: "string",  description: "Acción: 'U' (Guardar), 'D' (Eliminar). Vacío para listar.", example: "U"),
                new OA\Property(property: "part",        type: "string",  description: "Catálogo: 'BR' (Marca), 'MD' (Modelo), 'PT' (Repuesto)", example: "BR"),
                new OA\Property(property: "brandId",     type: "integer", description: "ID de marca", example: 0),
                new OA\Property(property: "brandNombre", type: "string",  description: "Nombre de marca (para part=BR)"),
                new OA\Property(property: "modelId",     type: "integer", description: "ID de modelo (para part=MD)"),
                new OA\Property(property: "modelNombre", type: "string",  description: "Nombre de modelo (para part=MD)"),
                new OA\Property(property: "pantalla",    type: "string",  description: "Tipo de pantalla (para part=MD)"),
                new OA\Property(property: "pdf_archivo", type: "string",  format: "binary", description: "PDF de esquema (para part=MD)"),
                new OA\Property(property: "partId",      type: "integer", description: "ID de repuesto (para part=PT)"),
                new OA\Property(property: "partNombre",  type: "string",  description: "Nombre del repuesto (para part=PT)"),
                new OA\Property(property: "partDesc",    type: "string",  description: "Descripción del repuesto (para part=PT)"),
                new OA\Property(property: "precioCrc",   type: "number",  description: "Precio CRC (para part=PT)"),
                new OA\Property(property: "stock",       type: "integer", description: "Stock (para part=PT)"),
            ])
        )
    ),
    responses: [new OA\Response(response: 200, description: "Respuesta JSON del sistema")]
)]
class CatalogsEndpoint {}
