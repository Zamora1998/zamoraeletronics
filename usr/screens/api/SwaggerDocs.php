<?php

namespace Screens\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Screens & Catalogs API",
    version: "1.0.0",
    description: "API de integración para la App Móvil de Técnicos (rzamoraelectronics). Maneja todo el flujo de órdenes, clientes, firmas y catálogos."
)]
#[OA\SecurityScheme(
    securityScheme: "cookieAuth",
    type: "apiKey",
    in: "cookie",
    name: "PHPSESSID"
)]
#[OA\Server(url: "/", description: "Servidor principal")]
class SwaggerInfo {}

// ==========================================
// SCHEMAS / COMPONENTES
// ==========================================

#[OA\Schema(schema: "GenericResponse", properties: [
    new OA\Property(property: "result", type: "boolean"),
    new OA\Property(property: "error", type: "string", nullable: true)
])]
class GenericResponse {}

// --- REQUESTS SCREENS ---
#[OA\Schema(schema: "ReqSaveClient", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "CL"),
    new OA\Property(property: "clientId", type: "integer", example: 0),
    new OA\Property(property: "nombre", type: "string"),
    new OA\Property(property: "telefono", type: "string"),
    new OA\Property(property: "ubicacion", type: "string"),
    new OA\Property(property: "latitud", type: "number", format: "float"),
    new OA\Property(property: "longitud", type: "number", format: "float")
])]
class ReqSaveClient {}

#[OA\Schema(schema: "ReqSaveOrder", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "OR"),
    new OA\Property(property: "orderId", type: "integer", example: 0),
    new OA\Property(property: "clientId", type: "integer"),
    new OA\Property(property: "brandId", type: "integer"),
    new OA\Property(property: "modelId", type: "integer"),
    new OA\Property(property: "modeloLibre", type: "string"),
    new OA\Property(property: "pantallaLibre", type: "string"),
    new OA\Property(property: "fallaReportada", type: "string"),
    new OA\Property(property: "costoEstimado", type: "number"),
    new OA\Property(property: "abonoInicial", type: "number"),
    new OA\Property(property: "estado", type: "string", example: "pendiente"),
    new OA\Property(property: "tipoPago", type: "string", example: "pendiente"),
    new OA\Property(property: "notas", type: "string")
])]
class ReqSaveOrder {}

#[OA\Schema(schema: "ReqUpdateStatus", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "ST"),
    new OA\Property(property: "orderId", type: "integer"),
    new OA\Property(property: "estado", type: "string"),
    new OA\Property(property: "tipoPago", type: "string")
])]
class ReqUpdateStatus {}

#[OA\Schema(schema: "ReqSaveSignature", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "FI"),
    new OA\Property(property: "orderId", type: "integer"),
    new OA\Property(property: "firmaBase64", type: "string", description: "PNG base64 string")
])]
class ReqSaveSignature {}

#[OA\Schema(schema: "ReqSaveOrderPart", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "OP"),
    new OA\Property(property: "orderId", type: "integer"),
    new OA\Property(property: "orderPartId", type: "integer", example: 0),
    new OA\Property(property: "partId", type: "integer"),
    new OA\Property(property: "cantidad", type: "integer", example: 1),
    new OA\Property(property: "precioUnit", type: "number")
])]
class ReqSaveOrderPart {}

#[OA\Schema(schema: "ReqDelete", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "D"),
    new OA\Property(property: "part", type: "string", example: "CL, OR, o OP"),
    new OA\Property(property: "clientId", type: "integer", description: "Para part=CL"),
    new OA\Property(property: "orderId", type: "integer", description: "Para part=OR u OP"),
    new OA\Property(property: "orderPartId", type: "integer", description: "Para part=OP")
])]
class ReqDelete {}

// --- REQUESTS CATALOGS ---
#[OA\Schema(schema: "ReqSaveBrand", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "BR"),
    new OA\Property(property: "brandId", type: "integer", example: 0),
    new OA\Property(property: "brandNombre", type: "string")
])]
class ReqSaveBrand {}

#[OA\Schema(schema: "ReqSaveModel", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "MD"),
    new OA\Property(property: "modelId", type: "integer", example: 0),
    new OA\Property(property: "brandId", type: "integer"),
    new OA\Property(property: "modelNombre", type: "string"),
    new OA\Property(property: "pantalla", type: "string"),
    new OA\Property(property: "pdf_archivo", type: "string", format: "binary", description: "Archivo PDF (multipart/form-data)")
])]
class ReqSaveModel {}

#[OA\Schema(schema: "ReqSavePart", type: "object", properties: [
    new OA\Property(property: "action", type: "string", example: "U"),
    new OA\Property(property: "part", type: "string", example: "PT"),
    new OA\Property(property: "partId", type: "integer", example: 0),
    new OA\Property(property: "brandId", type: "integer"),
    new OA\Property(property: "partNombre", type: "string"),
    new OA\Property(property: "partDesc", type: "string"),
    new OA\Property(property: "precioCrc", type: "number"),
    new OA\Property(property: "stock", type: "integer")
])]
class ReqSavePart {}


// ==========================================
// ENDPOINTS
// ==========================================

#[OA\Post(
    path: "/api/screens",
    summary: "Transacciones: Guardar y Eliminar (Clientes, Órdenes, Partes, Firma, Estado)",
    description: "Endpoint principal para transacciones de negocio. Puedes usar application/x-www-form-urlencoded o multipart/form-data.",
    security: [["cookieAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "application/x-www-form-urlencoded",
            schema: new OA\Schema(
                oneOf: [
                    new OA\Schema(ref: "#/components/schemas/ReqSaveClient"),
                    new OA\Schema(ref: "#/components/schemas/ReqSaveOrder"),
                    new OA\Schema(ref: "#/components/schemas/ReqUpdateStatus"),
                    new OA\Schema(ref: "#/components/schemas/ReqSaveSignature"),
                    new OA\Schema(ref: "#/components/schemas/ReqSaveOrderPart"),
                    new OA\Schema(ref: "#/components/schemas/ReqDelete")
                ]
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "OK", content: new OA\JsonContent(ref: "#/components/schemas/GenericResponse"))
    ]
)]
class ScreensPostEndpoint {}

#[OA\Get(
    path: "/api/screens",
    summary: "Consultas: Obtener Clientes, Órdenes, y Detalles",
    description: "Para listar órdenes (`part=OR`), clientes (`part=CL`) o detalle de orden (`part=ORD`).",
    security: [["cookieAuth" => []]],
    parameters: [
        new OA\Parameter(name: "part", in: "query", required: true, description: "Módulo ('OR', 'CL', 'ORD')", schema: new OA\Schema(type: "string", example: "OR")),
        new OA\Parameter(name: "clientId", in: "query", required: false, description: "Filtro cliente (para OR)", schema: new OA\Schema(type: "integer")),
        new OA\Parameter(name: "estado", in: "query", required: false, description: "Filtro estado (para OR)", schema: new OA\Schema(type: "string")),
        new OA\Parameter(name: "orderId", in: "query", required: false, description: "Requerido para ORD", schema: new OA\Schema(type: "integer"))
    ],
    responses: [
        new OA\Response(response: 200, description: "OK", content: new OA\JsonContent(ref: "#/components/schemas/GenericResponse"))
    ]
)]
class ScreensGetEndpoint {}

#[OA\Post(
    path: "/api/screens/catalogs",
    summary: "Transacciones de Catálogos (Marcas, Modelos, Repuestos)",
    description: "Maneja creación, actualización y borrado de catálogos.",
    security: [["cookieAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                oneOf: [
                    new OA\Schema(ref: "#/components/schemas/ReqSaveBrand"),
                    new OA\Schema(ref: "#/components/schemas/ReqSaveModel"),
                    new OA\Schema(ref: "#/components/schemas/ReqSavePart"),
                    new OA\Schema(ref: "#/components/schemas/ReqDelete")
                ]
            )
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "OK", content: new OA\JsonContent(ref: "#/components/schemas/GenericResponse"))
    ]
)]
class CatalogsPostEndpoint {}

#[OA\Get(
    path: "/api/screens/catalogs",
    summary: "Consultas de Catálogos",
    description: "Para listar marcas (`part=BR`), modelos (`part=MD`) y repuestos (`part=PT`).",
    security: [["cookieAuth" => []]],
    parameters: [
        new OA\Parameter(name: "part", in: "query", required: true, description: "Módulo ('BR', 'MD', 'PT')", schema: new OA\Schema(type: "string", example: "BR")),
        new OA\Parameter(name: "brandId", in: "query", required: false, description: "Filtrar modelos o partes por marca", schema: new OA\Schema(type: "integer"))
    ],
    responses: [
        new OA\Response(response: 200, description: "OK", content: new OA\JsonContent(ref: "#/components/schemas/GenericResponse"))
    ]
)]
class CatalogsGetEndpoint {}
