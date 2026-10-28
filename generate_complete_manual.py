import sys
from pathlib import Path
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.section import WD_SECTION
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

ROOT = Path(r"C:\xampp\htdocs\Gourmetica")
IMG = ROOT / "storage" / "app"
OUT = ROOT / "Manual_Usuario_Gourmetica.docx"

def shade(cell, fill):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = tc_pr.find(qn('w:shd'))
    if shd is None:
        shd = OxmlElement('w:shd')
        tc_pr.append(shd)
    shd.set(qn('w:fill'), fill)

def set_cell_border(cell, color="D1D5DB", size="4"):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    borders = tcPr.first_child_found_in("w:tcBorders")
    if borders is None:
        borders = OxmlElement('w:tcBorders')
        tcPr.append(borders)
    for edge in ('top', 'left', 'bottom', 'right', 'insideH', 'insideV'):
        tag = 'w:' + edge
        element = borders.find(qn(tag))
        if element is None:
            element = OxmlElement(tag)
            borders.append(element)
        element.set(qn('w:val'), 'single')
        element.set(qn('w:sz'), size)
        element.set(qn('w:space'), '0')
        element.set(qn('w:color'), color)

def set_cell_margins(cell, top=120, start=140, bottom=120, end=140):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    tcMar = tcPr.first_child_found_in('w:tcMar')
    if tcMar is None:
        tcMar = OxmlElement('w:tcMar')
        tcPr.append(tcMar)
    for m, v in [('top', top), ('start', start), ('bottom', bottom), ('end', end)]:
        node = tcMar.find(qn('w:' + m))
        if node is None:
            node = OxmlElement('w:' + m)
            tcMar.append(node)
        node.set(qn('w:w'), str(v))
        node.set(qn('w:type'), 'dxa')

def add_page_number(paragraph):
    paragraph.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    run = paragraph.add_run('Página ')
    fld = OxmlElement('w:fldSimple')
    fld.set(qn('w:instr'), 'PAGE')
    paragraph._p.append(fld)

def add_section(doc, title, route, image_name, description, steps=None, notes=None, width_inch=6.1):
    doc.add_heading(title, level=2)
    
    # Metadata callout
    p_meta = doc.add_paragraph()
    r_lbl = p_meta.add_run("Ruta de acceso: ")
    r_lbl.bold = True
    r_lbl.font.color.rgb = RGBColor(183, 122, 62) # Gold brand accent
    p_meta.add_run(route)
    p_meta.paragraph_format.space_after = Pt(4)
    
    # Description
    p_desc = doc.add_paragraph(description)
    p_desc.paragraph_format.space_after = Pt(8)
    
    # Image
    image_path = IMG / image_name
    if image_path.exists():
        pic = doc.add_paragraph()
        pic.alignment = WD_ALIGN_PARAGRAPH.CENTER
        pic.paragraph_format.space_before = Pt(4)
        pic.paragraph_format.space_after = Pt(4)
        pic.add_run().add_picture(str(image_path), width=Inches(width_inch))
        
        cap = doc.add_paragraph(f"Figura: Interfaz de {title.lower()} con datos operativos de prueba.")
        cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
        cap.paragraph_format.space_after = Pt(10)
        cap.runs[0].italic = True
        cap.runs[0].font.size = Pt(8.5)
        cap.runs[0].font.color.rgb = RGBColor(107, 114, 128)
    else:
        doc.add_paragraph('[Captura no disponible en el almacenamiento]').italic = True
        
    # Steps
    if steps:
        p_st_title = doc.add_paragraph()
        p_st_title.add_run("Procedimiento de uso:").bold = True
        p_st_title.paragraph_format.space_before = Pt(4)
        p_st_title.paragraph_format.space_after = Pt(2)
        for step in steps:
            p_step = doc.add_paragraph(step, style='List Bullet')
            p_step.paragraph_format.space_after = Pt(2)
            
    # Notes / Tips
    if notes:
        p_note = doc.add_paragraph()
        p_note.paragraph_format.space_before = Pt(6)
        p_note.paragraph_format.space_after = Pt(12)
        r_n_lbl = p_note.add_run("📌 Consideraciones clave: ")
        r_n_lbl.bold = True
        r_n_lbl.font.color.rgb = RGBColor(15, 23, 42)
        p_note.add_run(notes)

def build():
    doc = Document()
    sec = doc.sections[0]
    sec.top_margin = Inches(0.7)
    sec.bottom_margin = Inches(0.7)
    sec.left_margin = Inches(0.8)
    sec.right_margin = Inches(0.8)

    # Styles
    styles = doc.styles
    styles['Normal'].font.name = 'Segoe UI'
    styles['Normal'].font.size = Pt(10)
    styles['Normal'].font.color.rgb = RGBColor(31, 41, 55)

    for name, size, color in [('Title', 26, '1E293B'), ('Heading 1', 17, 'B77A3E'), ('Heading 2', 12.5, '111827'), ('Heading 3', 11, '374151')]:
        st = styles[name]
        st.font.name = 'Segoe UI Semibold'
        st.font.size = Pt(size)
        st.font.bold = True
        st.font.color.rgb = RGBColor.from_string(color)

    header = sec.header.paragraphs[0]
    header.text = 'GOURMETICA  |  MANUAL OFICIAL DE USUARIO Y OPERACIONES'
    header.runs[0].font.size = Pt(8)
    header.runs[0].font.bold = True
    header.runs[0].font.color.rgb = RGBColor(156, 163, 175)

    footer = sec.footer.paragraphs[0]
    add_page_number(footer)
    footer.runs[0].font.size = Pt(8)
    footer.runs[0].font.color.rgb = RGBColor(156, 163, 175)

    # ==================== PORTADA ====================
    p_brand = doc.add_paragraph()
    p_brand.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_brand.paragraph_format.space_before = Pt(36)
    p_brand.paragraph_format.space_after = Pt(8)
    r_brand = p_brand.add_run('GOURMETICA')
    r_brand.font.name = 'Georgia'
    r_brand.font.size = Pt(34)
    r_brand.font.bold = True
    r_brand.font.color.rgb = RGBColor(183, 122, 62) # #B77A3E

    p_subbrand = doc.add_paragraph()
    p_subbrand.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_subbrand.paragraph_format.space_after = Pt(28)
    r_sub = p_subbrand.add_run('PASTELERÍA ARTESANAL & ALTA REPOSTERÍA')
    r_sub.font.size = Pt(10)
    r_sub.font.bold = True
    r_sub.font.color.rgb = RGBColor(100, 116, 139)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_after = Pt(12)
    r_title = p_title.add_run('MANUAL DE USUARIO Y OPERACIONES DEL SISTEMA')
    r_title.font.size = Pt(20)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(15, 23, 42)

    p_desc = doc.add_paragraph()
    p_desc.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_desc.paragraph_format.space_after = Pt(36)
    r_desc = p_desc.add_run('Guía integral con capturas reales y procedimientos de uso para la Tienda Digital y la Intranet de Gestión Integral')
    r_desc.italic = True
    r_desc.font.size = Pt(11)
    r_desc.font.color.rgb = RGBColor(75, 85, 99)

    # Metadata box
    t_meta = doc.add_table(rows=5, cols=2)
    t_meta.alignment = WD_TABLE_ALIGNMENT.CENTER
    meta_info = [
        ("Entorno Productivo / Demo:", "https://gourmetica.com.pe"),
        ("Acceso Administrativo:", "https://gourmetica.com.pe/admin/login"),
        ("Usuario Maestro Administrador:", "admin@gourmetica.com (Acceso Global)"),
        ("Fecha de Edición y Validación:", "Octubre / Noviembre de 2026"),
        ("Estado de Información:", "Validado con archivos y registros de prueba activos")
    ]
    for row_idx, (k, v) in enumerate(meta_info):
        c0, c1 = t_meta.rows[row_idx].cells
        c0.text = k
        c1.text = v
        shade(c0, 'F8FAFC'); shade(c1, 'FFFFFF')
        set_cell_border(c0); set_cell_border(c1)
        set_cell_margins(c0, 80, 120, 80, 120); set_cell_margins(c1, 80, 120, 80, 120)
        c0.paragraphs[0].runs[0].font.bold = True
        c0.paragraphs[0].runs[0].font.size = Pt(9.5)
        c1.paragraphs[0].runs[0].font.size = Pt(9.5)

    doc.add_page_break()

    # ==================== ÍNDICE ====================
    doc.add_heading('Estructura del Manual de Usuario', level=1)
    doc.add_paragraph('El presente manual documenta el ecosistema digital completo de Gourmetica, dividido en dos grandes bloques: la plataforma pública de atención a comensales y el portal intranet para la gestión administrativa, logística y de cocina.')

    toc_data = [
        ('Módulo 1', 'Plataforma Pública y Tienda Digital', 'Página de inicio, catálogo interactivo, ficha de producto, carrito artesanal, sedes, catering y login de clientes.'),
        ('Módulo 2', 'Intranet Administrativa y Operaciones', 'Acceso seguro, dashboard ejecutivo, pedidos en vivo (caja), gestión de pedidos, boletas y comandas térmicas.'),
        ('Módulo 3', 'Logística, Inventario y Producción', 'Control de materia prima (insumos), recetas de producción, gestión de compras y abastecimiento.'),
        ('Módulo 4', 'Catálogo Comercial y Configuración', 'Gestión de productos, categorías, sedes, delivery por zonas, banners y parámetros del sistema.'),
        ('Módulo 5', 'Directorio de Usuarios y Seguridad', 'Control de administradores, asignación de sedes, matriz de permisos y buenas prácticas.'),
    ]
    t_toc = doc.add_table(rows=1, cols=3)
    t_toc.alignment = WD_TABLE_ALIGNMENT.CENTER
    t_toc.style = 'Table Grid'
    for cell, txt in zip(t_toc.rows[0].cells, ['Módulo', 'Área Funcional', 'Alcance y Pantallas Documentadas']):
        cell.text = txt
        shade(cell, '1E293B')
        set_cell_border(cell)
        set_cell_margins(cell, 100, 120, 100, 120)
        r = cell.paragraphs[0].runs[0]
        r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)

    for idx, item in enumerate(toc_data):
        cells = t_toc.add_row().cells
        cells[0].text, cells[1].text, cells[2].text = item
        for c in cells:
            set_cell_border(c)
            set_cell_margins(c, 90, 110, 90, 110)
            c.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
            if idx % 2 == 1:
                shade(c, 'F8FAFC')
        cells[0].paragraphs[0].runs[0].font.bold = True

    doc.add_paragraph().paragraph_format.space_before = Pt(14)
    doc.add_page_break()

    # ==================== MÓDULO 1 ====================
    doc.add_heading('1. Plataforma Pública y Tienda en Línea', level=1)
    doc.add_paragraph('La interfaz pública de Gourmetica brinda una experiencia cálida, moderna y optimizada para la navegación visual de la pastelería artesanal, facilitando la compra directa, selección de sedes y cotización de eventos.')

    # 1.1 Home
    add_section(
        doc,
        '1.1 Página de Inicio (Home)',
        '/',
        'manual-home.png',
        'La página de inicio presenta la identidad de marca, propuesta de valor culinaria y acceso rápido a las cartas de productos destacados, sedes y promociones vigentes.',
        steps=[
            'Use el botón "Ver Carta" o "Hacer Pedido" para ingresar directamente al catálogo con precios actualizados según la sede elegida.',
            'Consulte en el encabezado la sede actualmente activa (por ejemplo, "Sede Central Lima" o "Chiclayo") y haga clic en ella si desea cambiar el punto de entrega/recojo.',
            'Revise las secciones promocionales inferiores para conocer lanzamientos, recomendaciones del chef y enlaces directos a catering.'
        ],
        notes='La sede activa seleccionada en la cabecera determina la lista de productos con stock y los precios disponibles en el catálogo.'
    )

    # 1.2 Catálogo
    add_section(
        doc,
        '1.2 Catálogo Digital y Carta de Productos',
        '/shop',
        'manual-catalogo.png',
        'El catálogo clasifica los productos en categorías especializadas (Tortas, Postres Personales, Porciones Saludables, Alfajores, etc.). Permite filtrar instantáneamente y visualizar fotos, precios y disponibilidad.',
        steps=[
            'Utilice la barra horizontal sticky de categorías para filtrar por tipo de producto (Todos, Postres, Tortas, etc.).',
            'Cada tarjeta exhibe la imagen, nombre, descripción breve y precio base de la sede.',
            'Haga clic en el botón "Ver más" de cualquier producto para abrir la ficha detallada con selección de tamaños y opciones.'
        ],
        notes='Los productos que no cuenten con existencias en la sede seleccionada se marcan o no se listan según las reglas de stock de la sede.'
    )

    # 1.3 Ficha de Producto
    add_section(
        doc,
        '1.3 Ficha de Detalle de Producto y Personalización',
        '/shop/product/{slug}',
        'manual-producto-detalle.png',
        'Permite al cliente examinar especificaciones del postre, elegir porción individual o torta entera de 12 porciones, ajustar cantidades y añadir al carrito.',
        steps=[
            'Seleccione el tamaño deseado: "Porción" o "Entera (12 porciones)". El precio total se actualizará dinámicamente.',
            'Ajuste la cantidad requerida mediante los controles numéricos (- / +).',
            'Presione el botón "AÑADIR AL CARRITO" para registrar el producto en la sesión de compra.'
        ],
        notes='Si el producto cuenta con modificadores o ingredientes especiales configurados, estos se calcularán automáticamente sobre el precio final.'
    )

    # 1.4 Carrito
    add_section(
        doc,
        '1.4 Carrito de Compras Artesanal',
        '/cart',
        'manual-carrito.png',
        'Pantalla de consolidación del pedido. El cliente puede revisar los artículos elegidos, verificar la porción o tamaño seleccionado, controlar cantidades, eliminar productos y revisar el total estimado.',
        steps=[
            'Revise los productos seleccionados y verifique que el tamaño (por ejemplo, "PORCIÓN" o "ENTERA") sea el deseado.',
            'Si desea añadir más artículos, presione "SEGUIR COMPRANDO" para retornar al catálogo.',
            'Para continuar con la orden, presione "FINALIZAR PEDIDO".'
        ],
        notes='Si cambia de sede mientras tiene productos en el carrito, el sistema recalculará los precios y notificará si algún artículo no está disponible en la nueva ubicación.'
    )

    # 1.5 Sedes
    add_section(
        doc,
        '1.5 Directorio de Sedes y Puntos de Atención',
        '/locations',
        'manual-sedes.png',
        'Muestra información completa sobre cada una de las casas Gourmetica activas (dirección exacta, teléfonos de contacto, horarios y facilidades para comensales).',
        steps=[
            'Identifique la sede más cercana a su ubicación.',
            'Consulte los números de atención telefónica o haga clic para enlazar directamente con la sede correspondiente.'
        ]
    )

    # 1.6 Catering Público
    add_section(
        doc,
        '1.6 Solicitud de Catering y Eventos Especiales',
        '/catering',
        'manual-catering.png',
        'Formulario interactivo para que clientes corporativos y particulares soliciten cotizaciones personalizadas de mesas dulces, bocaditos y estaciones de bebidas.',
        steps=[
            'Complete el formulario indicando Nombre Completo, Teléfono WhatsApp, Correo Electrónico y Sede preferida.',
            'Seleccione el Tipo de Evento (Corporativo, Matrimonio, Cumpleaños, etc.), la Fecha proyectada y el Número estimado de invitados.',
            'Detalle los requerimientos especiales en el cuadro de mensaje y envíe la solicitud.'
        ],
        notes='La solicitud ingresa instantáneamente a la Intranet en la sección de Catering para que el equipo comercial la evalúe y cotice.'
    )

    # 1.7 Contacto
    add_section(
        doc,
        '1.7 Canales de Atención y Contacto',
        '/contact',
        'manual-contacto.png',
        'Página de contacto oficial con teléfonos, correo de soporte, enlace a WhatsApp corporativo y ubicación de la sede central.'
    )

    # 1.8 Login Clientes
    add_section(
        doc,
        '1.8 Portal de Acceso para Clientes',
        '/login',
        'manual-login.png',
        'Permite a los comensales iniciar sesión con su correo o a través de Google para consultar sus pedidos realizados, compras anteriores y favoritos.'
    )

    doc.add_page_break()

    # ==================== MÓDULO 2 ====================
    doc.add_heading('2. Intranet Administrativa y Operativa', level=1)
    doc.add_paragraph('La Intranet de Gourmetica centraliza la gestión operativa de pedidos, caja física, facturación electrónica con SUNAT, control de cocina, almacén de insumos y parametrización general.')

    # 2.1 Acceso Administrativo
    add_section(
        doc,
        '2.1 Acceso Seguro a la Intranet (Admin Login)',
        '/admin/login',
        'manual-admin-login.png',
        'Página de autenticación para administradores generales, administradores de sede, cajeros e ingenieros de soporte.',
        steps=[
            'Ingrese el correo electrónico registrado del personal administrativo.',
            'Escriba la contraseña correspondiente y haga clic en "INICIAR SESIÓN".',
            'Al validarse satisfactoriamente, el sistema redirige al Panel de Control según los permisos asignados al rol.'
        ],
        notes='Por políticas de seguridad, las cuentas son personales e intransferibles. Cierre sesión al finalizar su turno en cualquier terminal compartida.'
    )

    # 2.2 Dashboard
    add_section(
        doc,
        '2.2 Panel de Control Principal (Dashboard y KPIs)',
        '/intranet/dashboard',
        'manual-dashboard-wide.png',
        'Centro de mando gerencial con métricas en tiempo real: ventas totales del día, stock activo, compras registradas, sedes operativas, balance de caja chica y desglose de recaudación por sede.',
        steps=[
            'Monitoree los 4 indicadores superiores (Ventas Totales, Productos en Stock, Compras Realizadas y Sedes Activas).',
            'Revise el cuadro de "Resumen de Flujo de Caja" para contrastar Ingresos vs Egresos y el Balance Neto en caja.',
            'Consulte la tabla inferior de "Sedes Bajo tu Cargo" para acceder rápidamente a la administración de cada sucursal.'
        ],
        notes='El Administrador General visualiza la consolidación de todas las sedes, mientras que un Administrador de Sede solo visualiza las cifras de su local asignado.'
    )

    # 2.3 Pedidos en Vivo y Caja
    add_section(
        doc,
        '2.3 Punto de Venta y Pedidos en Vivo (Caja / POS)',
        '/intranet/live-orders',
        'manual4-live-orders.png',
        'Módulo operativo de caja rápida para registrar ventas presenciales directas y seguir en tiempo real el flujo de pedidos entrantes en formato Kanban: Novedad, Preparando, Listo para entregar y Entregado.',
        steps=[
            'Use el botón "+ REGISTRAR VENTA FÍSICA" para abrir el modal de venta rápida en mostrador.',
            'Monitoree las columnas de pedidos activos según su avance en preparación.',
            'Haga clic sobre cualquier pedido para ver su comanda de cocina o generar el ticket de pago.'
        ]
    )

    # 2.4 Gestión de Pedidos
    add_section(
        doc,
        '2.4 Gestión de Pedidos Online y Presenciales',
        '/intranet/orders',
        'manual4-pedidos.png',
        'Listado maestro de pedidos emitidos en el sistema. Registra código único (#00014), fecha/hora, nombre del cliente, sede preparadora, monto total, estado operativo y accesos rápidos.',
        steps=[
            'Consulte los pedidos filtrados por estado o sede.',
            'Cambie directamente el estado del pedido (Pendiente, En Preparación, Listo, Entregado) desde el selector desplegable.',
            'Haga clic en el ícono de ojo para abrir el detalle completo del pedido, o en los íconos de impresora para boletas y comandas.'
        ]
    )

    # 2.5 Detalle de Pedido
    add_section(
        doc,
        '2.5 Detalle de Pedido y Despacho',
        '/intranet/orders/{id}',
        'manual4-pedido-detalle.png',
        'Ficha detallada del pedido que desglosa productos contratados, subtotales, datos de contacto del cliente, dirección o modalidad de entrega, método de pago y herramientas de comunicación directa por WhatsApp.',
        steps=[
            'Verifique los productos y porciones ordenadas en la tabla "Detalle de Productos".',
            'En el panel derecho, actualice el estado del pedido conforme progrese la atención y presione "GUARDAR ESTADO".',
            'Utilice las opciones de "Formatos Comanda y Boleta / Factura" para copiar el texto de la comanda o enviarlo con un solo clic al WhatsApp del comensal o repartidor.'
        ]
    )

    # 2.6 Comanda y Boleta Térmica
    add_section(
        doc,
        '2.6 Emisión de Boleta Electrónica y Comanda de Cocina',
        '/intranet/orders/{id}/ticket & /comanda',
        'manual4-ticket-print.png',
        'Generador de comprobantes y órdenes en formato de tirilla térmica de 80mm/58mm. Incluye logotipo oficial, RUC, número de serie-correlativo electrónico y resumen tributario.',
        steps=[
            'Presione el botón "IMPRIMIR TICKET" para enviar directamente a la impresora térmica de mostrador.',
            'Utilice "COPIAR WHATSAPP" o "ENVIAR WHATSAPP" para remitir una copia digital instantánea al cliente sin requerir papel.'
        ]
    )

    # 2.7 Catering Admin
    add_section(
        doc,
        '2.7 Gestión de Solicitudes de Catering',
        '/intranet/catering',
        'manual4-catering-admin.png',
        'Bandeja de solicitudes de catering recibidas desde el portal público. Muestra el código (#00001), cliente, tipo de evento (Corporativo, Boda, etc.), fecha proyectada, cantidad de comensales y estado de cotización.',
        steps=[
            'Haga clic en "Ver Detalles >" sobre cualquier solicitud para abrir el expediente comercial del evento.'
        ]
    )

    # 2.8 Catering Detalle
    add_section(
        doc,
        '2.8 Expediente y Cotización de Catering',
        '/intranet/catering/{id}',
        'manual4-catering-detalle.png',
        'Vista de trabajo donde se detallan las solicitudes del cliente (por ejemplo: "Mesa de bocaditos dulces y salados, menaje y estación de bebidas"), días faltantes para el evento y botones para contacto directo por WhatsApp.',
        steps=[
            'Revise los requerimientos específicos del evento.',
            'Haga clic en "Contactar por WhatsApp" para coordinar de inmediato con el cliente usando el número registrado.',
            'Actualice el estado del expediente: Pendiente, Revisado (Evaluando), Contactado (Cotización Enviada) o Completado (Venta Cerrada).'
        ]
    )

    # 2.9 Ventas y SUNAT
    add_section(
        doc,
        '2.9 Historial de Ventas y Declaración SUNAT',
        '/intranet/sales',
        'manual4-ventas.png',
        'Control contable de ingresos y comprobantes electrónicos emitidos. Exhibe el correlativo oficial (B001-00000500), monto total y el estado de declaración fiscal ante la SUNAT.',
        steps=[
            'Verifique las ventas con estado "PENDIENTE".',
            'Al validar el comprobante, presione el botón dorado "DECLARAR" para procesar el envío electrónico a la plataforma de SUNAT.',
            'Use el botón "+ REGISTRAR VENTA FÍSICA" para asentar ingresos de mostrador que no provengan de la tienda web.'
        ],
        notes='La declaración electrónica ante SUNAT debe realizarse en cumplimiento con los plazos tributarios vigentes para comprobantes de pago electrónicos.'
    )

    # 2.10 Clientes
    add_section(
        doc,
        '2.10 Directorio Central de Clientes',
        '/intranet/clients',
        'manual4-clientes.png',
        'Directorio unificado de clientes registrados en el portal y clientes creados en caja. Permite consultar nombres, teléfonos, correos y sedes preferidas.'
    )

    # 2.11 Cliente Detalle
    add_section(
        doc,
        '2.11 Perfil Consolidado e Historial del Cliente',
        '/intranet/clients/{id}',
        'manual4-cliente-detalle.png',
        'Ficha 360° del comensal. Consolida el Total Consumido acumulado (S/ 100.00), cantidad de compras online, compras en tienda física (POS) y las tablas completas de todos sus pedidos y comprobantes emitidos.',
        steps=[
            'Revise el comportamiento de compra del cliente y su ticket promedio acumulado.',
            'Acceda directamente a cualquiera de los pedidos previos para reimprimir comprobantes o revisar especificaciones de compras pasadas.'
        ]
    )

    # 2.12 Insumos
    add_section(
        doc,
        '2.12 Inventario de Insumos y Materia Prima',
        '/intranet/supplies',
        'manual4-insumos.png',
        'Módulo de control de existencias de ingredientes, insumos de pastelería, empaques y envases (78 insumos registrados: harinas, papeles film, envases kraft, cajas, etc.).',
        steps=[
            'Use "+ NUEVO INSUMO" para registrar una nueva materia prima indicando nombre, unidad de medida (kg, gr, und, lt) y costo promedio.',
            'Consulte el stock distribuido por cada sede y utilice las acciones de edición para registrar mermas o ajustes manuales auditados.'
        ]
    )

    # 2.13 Producción y Cocina
    add_section(
        doc,
        '2.13 Producción de Cocina y Recetas',
        '/intranet/productions',
        'manual4-produccion.png',
        'Planificación de horneado y producción diaria. Registra lotes preparados de bizcochos, cremas y coberturas, descontando automáticamente los insumos consumidos del inventario general.'
    )

    # 2.14 Compras
    add_section(
        doc,
        '2.14 Registro de Compras a Proveedores',
        '/intranet/purchases',
        'manual4-compras.png',
        'Módulo de egresos donde se asientan las compras de insumos, número de factura de proveedor, montos pagados y fecha de recepción en almacén.'
    )

    # 2.15 Productos
    add_section(
        doc,
        '2.15 Catálogo de Productos y Precios por Sede',
        '/intranet/products',
        'manual4-productos.png',
        'Gestión del catálogo maestro. Permite activar o desactivar productos globalmente, asignar categorías, fijar el precio base y establecer existencias y precios diferenciados por cada sede (ejemplo: Sede Central Lima vs Chiclayo).',
        steps=[
            'Presione "+ NUEVO PRODUCTO" para subir un nuevo postre con su foto, descripción y precio.',
            'Filtre por sede para auditar existencias y precios locales.',
            'Edite cualquier producto para ajustar su disponibilidad inmediata en la tienda digital.'
        ]
    )

    # 2.16 Categorías
    add_section(
        doc,
        '2.16 Administración de Categorías',
        '/intranet/categories',
        'manual4-categorias.png',
        'Organización del menú en categorías y familias de postres para ordenar la navegación del cliente.'
    )

    # 2.17 Sedes Admin
    add_section(
        doc,
        '2.17 Gestión de Sedes y Sucursales',
        '/intranet/headquarters',
        'manual4-sedes-admin.png',
        'Administración de locales físicos. Permite habilitar o inhabilitar sedes, actualizar direcciones, horarios de atención y teléfonos de contacto.'
    )

    # 2.18 Zonas Delivery
    add_section(
        doc,
        '2.18 Configuración de Zonas de Delivery y Tarifas',
        '/intranet/delivery-zones',
        'manual4-zonas-delivery.png',
        'Configura los distritos y radios de cobertura de despacho, fijando el costo de envío correspondiente y los tiempos estimados de entrega.'
    )

    # 2.19 Banners
    add_section(
        doc,
        '2.19 Banners Promocionales y Campañas',
        '/intranet/banners',
        'manual4-banners.png',
        'Permite publicar y programar los anuncios destacados y fotografías del carrusel principal de la tienda en línea.'
    )

    # 2.20 Usuarios
    add_section(
        doc,
        '2.20 Usuarios del Sistema y Roles de Seguridad',
        '/intranet/users',
        'manual4-usuarios.png',
        'Control de acceso y credenciales del personal. Define roles con permisos acotados: Administrador General (acceso global), Administrador de Sede (limitado a su sede), Cajero (operación POS) e Ingeniero de Sistemas.',
        steps=[
            'Asigne siempre el principio de menor privilegio: los cajeros solo deben tener permisos de venta rápida y comandas.',
            'Vinculación de sede: asegúrese de vincular a cada administrador local a su respectiva sucursal.'
        ]
    )

    # 2.21 Configuración
    add_section(
        doc,
        '2.21 Parámetros Generales del Sistema',
        '/intranet/settings',
        'manual4-configuracion.png',
        'Configuración de parámetros operativos: datos fiscales de la empresa, teléfono central de WhatsApp, correo de notificaciones y políticas de atención.'
    )

    doc.add_page_break()

    # ==================== MÓDULO 3: MATRIZ Y PROCEDIMIENTOS ====================
    doc.add_heading('3. Matriz de Roles y Buenas Prácticas Operativas', level=1)
    doc.add_paragraph('Para asegurar la integridad del inventario, la correcta facturación y la seguridad del sistema, todo el personal debe cumplir las siguientes pautas:')

    # Roles Table
    t_roles = doc.add_table(rows=1, cols=3)
    t_roles.alignment = WD_TABLE_ALIGNMENT.CENTER
    t_roles.style = 'Table Grid'
    for c, h in zip(t_roles.rows[0].cells, ['Rol del Personal', 'Alcance y Permisos', 'Funciones Operativas Principales']):
        c.text = h; shade(c, '1E293B'); set_cell_border(c); set_cell_margins(c, 100, 120, 100, 120)
        c.paragraphs[0].runs[0].font.bold = True
        c.paragraphs[0].runs[0].font.color.rgb = RGBColor(255, 255, 255)

    roles_data = [
        ('Administrador General', 'Acceso Global sin restricciones', 'Visualización de KPIs de todas las sedes, gestión de precios base, creación de usuarios, declaración SUNAT, configuración de sedes y auditoría.'),
        ('Administrador de Sede', 'Restringido a su sede asignada', 'Control de stock local, recepción de compras, pedidos de la sede, supervisión de caja y atención de catering asignado.'),
        ('Cajero / POS', 'Módulo de caja y ventas en vivo', 'Registro de ventas directas de mostrador, cambio de estado de pedidos a "Listo"/"Entregado", emisión de tickets y comandas térmicas.'),
        ('Ingeniero de Sistemas', 'Mantenimiento y soporte técnico', 'Monitoreo de logs, pruebas de recuperación de desastres (DR Drill), copias de seguridad de base de datos y parámetros de pasarela de pago.')
    ]
    for idx, r_item in enumerate(roles_data):
        cells = t_roles.add_row().cells
        cells[0].text, cells[1].text, cells[2].text = r_item
        for c in cells:
            set_cell_border(c); set_cell_margins(c, 90, 110, 90, 110)
            c.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
            if idx % 2 == 1: shade(c, 'F8FAFC')
        cells[0].paragraphs[0].runs[0].font.bold = True

    doc.add_paragraph().paragraph_format.space_before = Pt(12)

    doc.add_heading('Pautas de Seguridad y Operación Diaria', level=2)
    best_practices = [
        'Cierre de Turno en Caja: Al concluir cada jornada, el cajero debe verificar que la columna "Preparando" esté vacía y que todos los pedidos atendidos se encuentren en estado "Entregado".',
        'Validación de Stock antes de Publicar: Al crear o editar un producto, verifique que la sede tenga existencias activas asignadas para evitar quiebres de inventario en la tienda online.',
        'Declaración Oportuna a SUNAT: Las ventas pendientes de declarar deben procesarse antes del cierre del periodo fiscal mediante el botón "Declarar" en la intranet.',
        'Atención Rápida de Catering: Toda cotización de catering genera un plazo máximo sugerido de 24 horas para contactar al cliente vía WhatsApp o llamada telefónica.',
        'Resguardo de Credenciales: No compartir contraseñas administrativas ni operar con cuentas genéricas. Toda acción queda auditada con el usuario autenticado.'
    ]
    for bp in best_practices:
        p_bp = doc.add_paragraph(bp, style='List Bullet')
        p_bp.paragraph_format.space_after = Pt(4)

    # Save
    doc.core_properties.title = 'Manual de Usuario y Operaciones Gourmetica'
    doc.core_properties.subject = 'Guía oficial visual del sistema web e intranet Gourmetica'
    doc.core_properties.author = 'Gourmetica Repostería Fina'
    doc.save(OUT)
    print(f"DOCUMENT SAVED SUCCESSFULLY AT: {OUT}")

if __name__ == "__main__":
    build()
