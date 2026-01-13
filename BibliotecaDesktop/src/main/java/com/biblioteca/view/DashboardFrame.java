package com.biblioteca.view;

import com.biblioteca.controller.LibroController;
import com.biblioteca.controller.PrestamoController;
import com.biblioteca.controller.ClienteController;
import com.biblioteca.model.Libro;
import com.biblioteca.model.Prestamo;
import com.biblioteca.model.Cliente;
import javax.swing.*;
import javax.swing.table.*;
import java.awt.*;
import java.util.List;

public class DashboardFrame extends JFrame {
    private JTabbedPane tabbedPane;
    private JPanel panelInicio;
    private JPanel panelLibros;
    private JPanel panelPrestamos;
    private JPanel panelClientes;
    private JPanel panelCategorias;

    private JTable tablaLibros;
    private JTable tablaPrestamos;
    private DefaultTableModel modelLibros;
    private DefaultTableModel modelPrestamos;

    private LibroController libroController;
    private PrestamoController prestamoController;
    private ClienteController clienteController;

    // Colores principales
    private final Color COLOR_PRIMARIO = new Color(25, 118, 210);
    private final Color COLOR_SECUNDARIO = new Color(13, 71, 161);
    private final Color COLOR_FONDO = new Color(248, 249, 250);
    private final Color COLOR_TARJETA = Color.WHITE;
    private final Color COLOR_BORDE = new Color(230, 230, 230);

    public DashboardFrame() {
        setTitle("Dashboard - Sistema de Gestión de Biblioteca");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setSize(1400, 850);
        setLocationRelativeTo(null);

        // Establecer icono
        try {
            ImageIcon icon = new ImageIcon(getClass().getResource("/com/biblioteca/images/logo-biblioteca.png"));
            setIconImage(icon.getImage());
        } catch (Exception e) {
            System.out.println("Icono no encontrado");
        }

        // Inicializar controladores
        libroController = new LibroController();
        prestamoController = new PrestamoController();
        clienteController = new ClienteController();

        initComponents();
        cargarDatosInicio();
    }

    private void initComponents() {
        // Configurar fondo principal
        getContentPane().setBackground(COLOR_FONDO);

        // Crear barra de menú con estilo mejorado
        JMenuBar menuBar = new JMenuBar();
        menuBar.setBackground(COLOR_PRIMARIO);
        menuBar.setBorder(BorderFactory.createEmptyBorder(5, 10, 5, 10));

        JMenu menuArchivo = new JMenu("Archivo");
        menuArchivo.setForeground(Color.WHITE);
        menuArchivo.setFont(new Font("Segoe UI", Font.PLAIN, 14));

        JMenuItem itemSalir = new JMenuItem("Salir");
        itemSalir.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        itemSalir.addActionListener(e -> System.exit(0));
        menuArchivo.add(itemSalir);

        JMenu menuAyuda = new JMenu("Ayuda");
        menuAyuda.setForeground(Color.WHITE);
        menuAyuda.setFont(new Font("Segoe UI", Font.PLAIN, 14));

        JMenuItem itemAcerca = new JMenuItem("Acerca de");
        itemAcerca.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        menuAyuda.add(itemAcerca);

        menuBar.add(menuArchivo);
        menuBar.add(menuAyuda);

        // Panel de usuario
        JPanel userPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT));
        userPanel.setOpaque(false);

        JLabel lblUsuario = new JLabel("Administrador");
        lblUsuario.setFont(new Font("Segoe UI", Font.BOLD, 12));
        lblUsuario.setForeground(Color.WHITE);

        userPanel.add(lblUsuario);
        menuBar.add(Box.createHorizontalGlue());
        menuBar.add(userPanel);

        setJMenuBar(menuBar);

        // Panel de encabezado
        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(COLOR_PRIMARIO);
        headerPanel.setBorder(BorderFactory.createEmptyBorder(20, 30, 20, 30));

        JLabel lblTitulo = new JLabel("GESTIÓN DE BIBLIOTECA");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 28));
        lblTitulo.setForeground(Color.WHITE);

        JLabel lblSubtitulo = new JLabel("Sistema de administración integral");
        lblSubtitulo.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        lblSubtitulo.setForeground(new Color(200, 230, 255));

        JPanel titlePanel = new JPanel(new GridLayout(2, 1));
        titlePanel.setOpaque(false);
        titlePanel.add(lblTitulo);
        titlePanel.add(lblSubtitulo);

        headerPanel.add(titlePanel, BorderLayout.WEST);

        // Panel de estadísticas rápidas
        JPanel statsPanel = new JPanel(new GridLayout(1, 4, 10, 0));
        statsPanel.setOpaque(false);

        statsPanel.add(crearMiniTarjeta("Libros", "1,250"));
        statsPanel.add(crearMiniTarjeta("Clientes", "342"));
        statsPanel.add(crearMiniTarjeta("Préstamos", "48"));
        statsPanel.add(crearMiniTarjeta("Pendientes", "12"));

        headerPanel.add(statsPanel, BorderLayout.EAST);

        // Crear pestañas con estilo personalizado
        tabbedPane = new JTabbedPane();
        tabbedPane.setFont(new Font("Segoe UI", Font.BOLD, 14));
        tabbedPane.setBackground(COLOR_FONDO);

        panelInicio = crearPanelInicio();
        panelLibros = crearPanelLibros();
        panelPrestamos = crearPanelPrestamos();
        panelClientes = crearPanelClientes();
        panelCategorias = crearPanelCategorias();

        tabbedPane.addTab("Inicio", panelInicio);
        tabbedPane.addTab("Libros", panelLibros);
        tabbedPane.addTab("Préstamos", panelPrestamos);
        tabbedPane.addTab("Clientes", panelClientes);
        tabbedPane.addTab("Categorías", panelCategorias);

        // Configurar layout principal
        setLayout(new BorderLayout());
        add(headerPanel, BorderLayout.NORTH);
        add(tabbedPane, BorderLayout.CENTER);

        cargarLibros();
        cargarPrestamos();
    }

    private JPanel crearMiniTarjeta(String titulo, String valor) {
        JPanel tarjeta = new JPanel(new BorderLayout());
        tarjeta.setBackground(new Color(255, 255, 255, 30));
        tarjeta.setBorder(BorderFactory.createEmptyBorder(10, 15, 10, 15));

        JLabel lblTitulo = new JLabel(titulo);
        lblTitulo.setFont(new Font("Segoe UI", Font.PLAIN, 11));
        lblTitulo.setForeground(new Color(200, 230, 255));

        JLabel lblValor = new JLabel(valor);
        lblValor.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblValor.setForeground(Color.WHITE);

        JPanel contentPanel = new JPanel(new GridLayout(2, 1, 2, 2));
        contentPanel.setOpaque(false);
        contentPanel.add(lblTitulo);
        contentPanel.add(lblValor);

        tarjeta.add(contentPanel, BorderLayout.CENTER);
        return tarjeta;
    }

    private JPanel crearPanelInicio() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_FONDO);
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        JLabel lblTitulo = new JLabel("PANEL DE CONTROL");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 24));
        lblTitulo.setForeground(COLOR_SECUNDARIO);
        lblTitulo.setBorder(BorderFactory.createEmptyBorder(0, 0, 20, 0));

        // Panel de estadísticas principales
        JPanel panelStats = new JPanel(new GridLayout(2, 2, 15, 15));
        panelStats.setOpaque(false);

        int totalLibros = libroController.obtenerTodosLosLibros().size();
        int totalClientes = clienteController.obtenerTodosClientes().size();

        panelStats.add(crearTarjetaEstadistica("Total de Libros",
                String.valueOf(totalLibros), new Color(25, 118, 210)));
        panelStats.add(crearTarjetaEstadistica("Préstamos Activos",
                "0", new Color(255, 152, 0)));
        panelStats.add(crearTarjetaEstadistica("Clientes Registrados",
                String.valueOf(totalClientes), new Color(56, 142, 60)));
        panelStats.add(crearTarjetaEstadistica("Devoluciones Pendientes",
                "0", new Color(244, 67, 54)));

        // Panel de acciones rápidas
        JPanel panelAcciones = new JPanel(new GridLayout(1, 3, 10, 0));
        panelAcciones.setOpaque(false);
        panelAcciones.setBorder(BorderFactory.createEmptyBorder(20, 0, 0, 0));

        panelAcciones.add(crearBotonAccion("Nuevo Préstamo", new Color(25, 118, 210)));
        panelAcciones.add(crearBotonAccion("Registrar Libro", new Color(56, 142, 60)));
        panelAcciones.add(crearBotonAccion("Nuevo Cliente", new Color(255, 152, 0)));

        panel.add(lblTitulo, BorderLayout.NORTH);
        panel.add(panelStats, BorderLayout.CENTER);
        panel.add(panelAcciones, BorderLayout.SOUTH);

        return panel;
    }

    private JPanel crearTarjetaEstadistica(String titulo, String valor, Color color) {
        JPanel tarjeta = new JPanel(new BorderLayout());
        tarjeta.setBackground(COLOR_TARJETA);
        tarjeta.setBorder(BorderFactory.createLineBorder(COLOR_BORDE, 1));

        // Panel interno para padding
        JPanel innerPanel = new JPanel(new GridLayout(2, 1, 5, 5));
        innerPanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        innerPanel.setBackground(Color.WHITE);

        JLabel lblTitulo = new JLabel(titulo);
        lblTitulo.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        lblTitulo.setForeground(Color.DARK_GRAY);

        JLabel lblValor = new JLabel(valor);
        lblValor.setFont(new Font("Segoe UI", Font.BOLD, 32));
        lblValor.setForeground(color);
        lblValor.setHorizontalAlignment(SwingConstants.CENTER);

        innerPanel.add(lblTitulo);
        innerPanel.add(lblValor);

        tarjeta.add(innerPanel, BorderLayout.CENTER);
        return tarjeta;
    }

    private JButton crearBotonAccion(String texto, Color color) {
        JButton boton = new JButton(texto);
        boton.setFont(new Font("Segoe UI", Font.BOLD, 14));
        boton.setBackground(color);
        boton.setForeground(Color.BLACK);
        boton.setBorder(BorderFactory.createLineBorder(color.darker(), 1));
        boton.setFocusPainted(false);
        boton.setCursor(new Cursor(Cursor.HAND_CURSOR));

        boton.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                boton.setBackground(color.darker());
            }
            public void mouseExited(java.awt.event.MouseEvent evt) {
                boton.setBackground(color);
            }
        });

        return boton;
    }

    private JPanel crearPanelLibros() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_FONDO);
        panel.setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // Panel superior con título y búsqueda
        JPanel topPanel = new JPanel(new BorderLayout());
        topPanel.setOpaque(false);

        JLabel lblTitulo = new JLabel("GESTIÓN DE LIBROS");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblTitulo.setForeground(COLOR_SECUNDARIO);

        // Barra de búsqueda
        JPanel searchPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT));
        searchPanel.setOpaque(false);

        JTextField txtBuscar = new JTextField(18);
        txtBuscar.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        txtBuscar.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(6, 10, 6, 10)
        ));

        JButton btnBuscar = new JButton("Buscar");
        btnBuscar.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        btnBuscar.setBackground(COLOR_PRIMARIO);
        btnBuscar.setForeground(Color.BLACK);
        btnBuscar.setBorder(BorderFactory.createLineBorder(COLOR_PRIMARIO.darker(), 1));
        btnBuscar.setFocusPainted(false);

        searchPanel.add(new JLabel("Buscar:"));
        searchPanel.add(txtBuscar);
        searchPanel.add(btnBuscar);

        topPanel.add(lblTitulo, BorderLayout.WEST);
        topPanel.add(searchPanel, BorderLayout.EAST);

        // Panel de botones
        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT, 8, 8));
        panelBotones.setOpaque(false);
        panelBotones.setBorder(BorderFactory.createEmptyBorder(15, 0, 15, 0));

        JButton btnNuevo = crearBotonConIcono("Nuevo Libro", COLOR_PRIMARIO);
        JButton btnEditar = crearBotonConIcono("Editar", new Color(255, 152, 0));
        JButton btnEliminar = crearBotonConIcono("Eliminar", new Color(244, 67, 54));
        JButton btnActualizar = crearBotonConIcono("Actualizar", new Color(56, 142, 60));

        panelBotones.add(btnNuevo);
        panelBotones.add(btnEditar);
        panelBotones.add(btnEliminar);
        panelBotones.add(btnActualizar);

        // Configurar tabla
        String[] columnas = {"ID", "Título", "Autor", "ISBN", "Categoría", "Ejemplares", "Disponibles", "Ubicación"};
        modelLibros = new DefaultTableModel(columnas, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }
        };

        tablaLibros = new JTable(modelLibros);
        tablaLibros.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        tablaLibros.setRowHeight(30);
        tablaLibros.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        tablaLibros.getTableHeader().setBackground(COLOR_PRIMARIO);
        tablaLibros.getTableHeader().setForeground(Color.WHITE);
        tablaLibros.getTableHeader().setBorder(BorderFactory.createEmptyBorder());
        tablaLibros.setShowGrid(true);
        tablaLibros.setGridColor(new Color(240, 240, 240));
        tablaLibros.setSelectionBackground(new Color(220, 237, 255));
        tablaLibros.setSelectionForeground(Color.BLACK);

        // Personalizar renderizado de filas
        tablaLibros.setDefaultRenderer(Object.class, new DefaultTableCellRenderer() {
            @Override
            public Component getTableCellRendererComponent(JTable table, Object value,
                                                           boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = super.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);

                // Border sutil entre celdas
                setBorder(BorderFactory.createCompoundBorder(
                        BorderFactory.createMatteBorder(0, 0, 1, 1, new Color(240, 240, 240)),
                        BorderFactory.createEmptyBorder(0, 8, 0, 8)
                ));

                // Color de fondo alternado para filas
                if (!isSelected) {
                    if (row % 2 == 0) {
                        c.setBackground(Color.WHITE);
                    } else {
                        c.setBackground(new Color(250, 250, 250));
                    }
                }

                return c;
            }
        });

        // Ajustar anchos de columnas
        tablaLibros.getColumnModel().getColumn(0).setPreferredWidth(50);  // ID
        tablaLibros.getColumnModel().getColumn(1).setPreferredWidth(200); // Título
        tablaLibros.getColumnModel().getColumn(2).setPreferredWidth(150); // Autor
        tablaLibros.getColumnModel().getColumn(3).setPreferredWidth(120); // ISBN
        tablaLibros.getColumnModel().getColumn(4).setPreferredWidth(100); // Categoría
        tablaLibros.getColumnModel().getColumn(5).setPreferredWidth(80);  // Ejemplares
        tablaLibros.getColumnModel().getColumn(6).setPreferredWidth(80);  // Disponibles
        tablaLibros.getColumnModel().getColumn(7).setPreferredWidth(100); // Ubicación

        JScrollPane scrollPane = new JScrollPane(tablaLibros);
        scrollPane.setBorder(BorderFactory.createLineBorder(COLOR_BORDE, 1));
        scrollPane.getViewport().setBackground(Color.WHITE);

        // Acciones de botones
        btnActualizar.addActionListener(e -> cargarLibros());
        btnNuevo.addActionListener(e -> mostrarDialogoNuevoLibro());
        btnEditar.addActionListener(e -> editarLibroSeleccionado());
        btnEliminar.addActionListener(e -> eliminarLibroSeleccionado());

        panel.add(topPanel, BorderLayout.NORTH);
        panel.add(panelBotones, BorderLayout.CENTER);
        panel.add(scrollPane, BorderLayout.SOUTH);

        return panel;
    }

    private JPanel crearPanelPrestamos() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_FONDO);
        panel.setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        JLabel lblTitulo = new JLabel("GESTIÓN DE PRÉSTAMOS");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblTitulo.setForeground(COLOR_SECUNDARIO);
        lblTitulo.setBorder(BorderFactory.createEmptyBorder(0, 0, 15, 0));

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT, 8, 8));
        panelBotones.setOpaque(false);

        JButton btnNuevoPrestamo = crearBotonConIcono("Nuevo Préstamo", COLOR_PRIMARIO);
        JButton btnDevolver = crearBotonConIcono("Registrar Devolución", new Color(56, 142, 60));
        JButton btnActualizar = crearBotonConIcono("Actualizar", new Color(100, 100, 100));

        panelBotones.add(btnNuevoPrestamo);
        panelBotones.add(btnDevolver);
        panelBotones.add(btnActualizar);

        String[] columnas = {"ID", "Libro", "Cliente", "Fecha Préstamo", "Fecha Devolución", "Estado"};
        modelPrestamos = new DefaultTableModel(columnas, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }
        };

        tablaPrestamos = new JTable(modelPrestamos);
        tablaPrestamos.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        tablaPrestamos.setRowHeight(30);
        tablaPrestamos.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        tablaPrestamos.getTableHeader().setBackground(COLOR_PRIMARIO);
        tablaPrestamos.getTableHeader().setForeground(Color.WHITE);
        tablaPrestamos.getTableHeader().setBorder(BorderFactory.createEmptyBorder());

        // Personalizar renderizado
        tablaPrestamos.setDefaultRenderer(Object.class, new DefaultTableCellRenderer() {
            @Override
            public Component getTableCellRendererComponent(JTable table, Object value,
                                                           boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = super.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);

                // Border sutil entre celdas
                setBorder(BorderFactory.createCompoundBorder(
                        BorderFactory.createMatteBorder(0, 0, 1, 1, new Color(240, 240, 240)),
                        BorderFactory.createEmptyBorder(0, 8, 0, 8)
                ));

                // Color de fondo alternado para filas
                if (!isSelected) {
                    if (row % 2 == 0) {
                        c.setBackground(Color.WHITE);
                    } else {
                        c.setBackground(new Color(250, 250, 250));
                    }
                }

                // Color para estado
                if (column == 5 && "Activo".equals(value)) {
                    c.setForeground(new Color(56, 142, 60));
                } else if (column == 5 && "Vencido".equals(value)) {
                    c.setForeground(new Color(244, 67, 54));
                }

                return c;
            }
        });

        // Ajustar anchos de columnas
        tablaPrestamos.getColumnModel().getColumn(0).setPreferredWidth(50);  // ID
        tablaPrestamos.getColumnModel().getColumn(1).setPreferredWidth(200); // Libro
        tablaPrestamos.getColumnModel().getColumn(2).setPreferredWidth(150); // Cliente
        tablaPrestamos.getColumnModel().getColumn(3).setPreferredWidth(100); // Fecha Préstamo
        tablaPrestamos.getColumnModel().getColumn(4).setPreferredWidth(100); // Fecha Devolución
        tablaPrestamos.getColumnModel().getColumn(5).setPreferredWidth(80);  // Estado

        JScrollPane scrollPane = new JScrollPane(tablaPrestamos);
        scrollPane.setBorder(BorderFactory.createLineBorder(COLOR_BORDE, 1));
        scrollPane.getViewport().setBackground(Color.WHITE);

        btnActualizar.addActionListener(e -> cargarPrestamos());
        btnNuevoPrestamo.addActionListener(e -> mostrarDialogoNuevoPrestamo());

        panel.add(lblTitulo, BorderLayout.NORTH);
        panel.add(panelBotones, BorderLayout.CENTER);
        panel.add(scrollPane, BorderLayout.SOUTH);

        return panel;
    }

    private JPanel crearPanelClientes() {
        return new ClientesPanel(); // Usar el panel de clientes mejorado
    }

    private JPanel crearPanelCategorias() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_FONDO);
        panel.setBorder(BorderFactory.createEmptyBorder(40, 40, 40, 40));

        JLabel lblInfo = new JLabel(
                "<html><center><h1 style='color: #0D47A1; font-family: Segoe UI;'>GESTIÓN DE CATEGORÍAS</h1>" +
                        "<p style='font-size: 16px; color: #666; margin: 20px 0;'>Organiza y administra las categorías de libros de tu biblioteca</p>" +
                        "<p style='font-size: 14px; color: #888;'>Ejemplos: Novela, Ciencia, Historia, Infantil, Tecnología, Arte, etc.</p>" +
                        "<div style='margin-top: 30px; padding: 20px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;'>" +
                        "<p style='color: #0D47A1; font-weight: bold;'>Funcionalidades disponibles:</p>" +
                        "<ul style='text-align: left;'>" +
                        "<li>Crear nuevas categorías</li>" +
                        "<li>Editar categorías existentes</li>" +
                        "<li>Asignar libros a categorías</li>" +
                        "<li>Estadísticas por categoría</li>" +
                        "</ul></div></center></html>",
                SwingConstants.CENTER
        );

        panel.add(lblInfo, BorderLayout.CENTER);
        return panel;
    }

    private JButton crearBotonConIcono(String texto, Color color) {
        JButton boton = new JButton(texto);
        boton.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        boton.setBackground(color);
        boton.setForeground(Color.BLACK);
        boton.setBorder(BorderFactory.createLineBorder(color.darker(), 1));
        boton.setFocusPainted(false);
        boton.setCursor(new Cursor(Cursor.HAND_CURSOR));

        boton.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                boton.setBackground(color.darker());
            }
            public void mouseExited(java.awt.event.MouseEvent evt) {
                boton.setBackground(color);
            }
        });

        return boton;
    }

    private void cargarDatosInicio() {
        // Método para cargar datos adicionales
    }

    private void cargarLibros() {
        modelLibros.setRowCount(0);
        List<Libro> libros = libroController.obtenerTodosLosLibros();

        for (Libro libro : libros) {
            Object[] row = {
                    libro.getId(),
                    libro.getTitulo(),
                    libro.getAutor(),
                    libro.getIsbn(),
                    libro.getCategoria(),
                    libro.getEjemplares(),
                    libro.getDisponibles(),
                    libro.getUbicacion()
            };
            modelLibros.addRow(row);
        }
    }

    private void cargarPrestamos() {
        modelPrestamos.setRowCount(0);
        // Datos de ejemplo
        Object[] row1 = {1, "El Quijote", "Juan Pérez", "2024-01-15", "2024-02-15", "Activo"};
        Object[] row2 = {2, "Cien años de soledad", "María García", "2024-01-20", "2024-02-20", "Activo"};
        Object[] row3 = {3, "1984", "Carlos López", "2024-01-10", "2024-02-10", "Vencido"};

        modelPrestamos.addRow(row1);
        modelPrestamos.addRow(row2);
        modelPrestamos.addRow(row3);
    }

    private void mostrarDialogoNuevoLibro() {
        JDialog dialog = new JDialog(this, "Nuevo Libro", true);
        dialog.setSize(500, 500);
        dialog.setLocationRelativeTo(this);
        dialog.getContentPane().setBackground(Color.WHITE);

        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        panel.setBackground(Color.WHITE);

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Título del diálogo
        JLabel lblTituloDialog = new JLabel("REGISTRAR NUEVO LIBRO");
        lblTituloDialog.setFont(new Font("Segoe UI", Font.BOLD, 16));
        lblTituloDialog.setForeground(COLOR_SECUNDARIO);
        lblTituloDialog.setHorizontalAlignment(SwingConstants.CENTER);

        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.gridwidth = 2;
        gbc.anchor = GridBagConstraints.CENTER;
        panel.add(lblTituloDialog, gbc);

        gbc.gridwidth = 1;
        gbc.anchor = GridBagConstraints.WEST;

        // Campos del formulario
        gbc.gridx = 0;
        gbc.gridy = 1;
        JLabel lblTitulo = new JLabel("Título:");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 12));
        panel.add(lblTitulo, gbc);

        gbc.gridx = 1;
        gbc.gridy = 1;
        JTextField txtTitulo = new JTextField(20);
        txtTitulo.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        txtTitulo.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(6, 8, 6, 8)
        ));
        panel.add(txtTitulo, gbc);

        gbc.gridx = 0;
        gbc.gridy = 2;
        JLabel lblAutor = new JLabel("Autor:");
        lblAutor.setFont(new Font("Segoe UI", Font.BOLD, 12));
        panel.add(lblAutor, gbc);

        gbc.gridx = 1;
        gbc.gridy = 2;
        JTextField txtAutor = new JTextField(20);
        txtAutor.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        txtAutor.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(6, 8, 6, 8)
        ));
        panel.add(txtAutor, gbc);

        gbc.gridx = 0;
        gbc.gridy = 3;
        JLabel lblIsbn = new JLabel("ISBN:");
        lblIsbn.setFont(new Font("Segoe UI", Font.BOLD, 12));
        panel.add(lblIsbn, gbc);

        gbc.gridx = 1;
        gbc.gridy = 3;
        JTextField txtIsbn = new JTextField(20);
        txtIsbn.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        txtIsbn.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(6, 8, 6, 8)
        ));
        panel.add(txtIsbn, gbc);

        gbc.gridx = 0;
        gbc.gridy = 4;
        JLabel lblEjemplares = new JLabel("Ejemplares:");
        lblEjemplares.setFont(new Font("Segoe UI", Font.BOLD, 12));
        panel.add(lblEjemplares, gbc);

        gbc.gridx = 1;
        gbc.gridy = 4;
        JSpinner spnEjemplares = new JSpinner(new SpinnerNumberModel(1, 1, 100, 1));
        ((JSpinner.DefaultEditor) spnEjemplares.getEditor()).getTextField().setFont(new Font("Segoe UI", Font.PLAIN, 13));
        panel.add(spnEjemplares, gbc);

        // Panel de botones
        gbc.gridx = 0;
        gbc.gridy = 5;
        gbc.gridwidth = 2;
        gbc.fill = GridBagConstraints.NONE;
        gbc.anchor = GridBagConstraints.CENTER;

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.CENTER, 15, 0));
        panelBotones.setOpaque(false);

        JButton btnGuardar = new JButton("Guardar");
        btnGuardar.setFont(new Font("Segoe UI", Font.BOLD, 13));
        btnGuardar.setBackground(COLOR_PRIMARIO);
        btnGuardar.setForeground(Color.BLACK);
        btnGuardar.setBorder(BorderFactory.createLineBorder(COLOR_PRIMARIO.darker(), 1));
        btnGuardar.setFocusPainted(false);

        JButton btnCancelar = new JButton("Cancelar");
        btnCancelar.setFont(new Font("Segoe UI", Font.BOLD, 13));
        btnCancelar.setBackground(new Color(100, 100, 100));
        btnCancelar.setForeground(Color.BLACK);
        btnCancelar.setBorder(BorderFactory.createLineBorder(new Color(80, 80, 80), 1));
        btnCancelar.setFocusPainted(false);

        btnGuardar.addActionListener(e -> {
            Libro nuevoLibro = new Libro();
            nuevoLibro.setTitulo(txtTitulo.getText());
            nuevoLibro.setAutor(txtAutor.getText());
            nuevoLibro.setIsbn(txtIsbn.getText());
            nuevoLibro.setEjemplares((int)spnEjemplares.getValue());
            nuevoLibro.setDisponibles((int)spnEjemplares.getValue());

            if (libroController.agregarLibro(nuevoLibro)) {
                JOptionPane.showMessageDialog(dialog, "Libro guardado exitosamente");
                cargarLibros();
                dialog.dispose();
            } else {
                JOptionPane.showMessageDialog(dialog, "Error al guardar el libro", "Error", JOptionPane.ERROR_MESSAGE);
            }
        });

        btnCancelar.addActionListener(e -> dialog.dispose());

        panelBotones.add(btnGuardar);
        panelBotones.add(btnCancelar);
        panel.add(panelBotones, gbc);

        dialog.add(panel);
        dialog.setVisible(true);
    }

    private void editarLibroSeleccionado() {
        int selectedRow = tablaLibros.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un libro para editar",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
            return;
        }

        int id = (int) modelLibros.getValueAt(selectedRow, 0);
        Libro libro = libroController.buscarLibroPorId(id);

        if (libro != null) {
            JOptionPane.showMessageDialog(this,
                    "Editar libro ID: " + id + "\n(Funcionalidad en desarrollo)");
        }
    }

    private void eliminarLibroSeleccionado() {
        int selectedRow = tablaLibros.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un libro para eliminar",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
            return;
        }

        int id = (int) modelLibros.getValueAt(selectedRow, 0);
        String titulo = (String) modelLibros.getValueAt(selectedRow, 1);

        int confirm = JOptionPane.showConfirmDialog(this,
                "¿Está seguro de eliminar el libro:\n" + titulo + "?",
                "Confirmar eliminación",
                JOptionPane.YES_NO_OPTION,
                JOptionPane.WARNING_MESSAGE);

        if (confirm == JOptionPane.YES_OPTION) {
            if (libroController.eliminarLibro(id)) {
                JOptionPane.showMessageDialog(this, "Libro eliminado exitosamente");
                cargarLibros();
            } else {
                JOptionPane.showMessageDialog(this,
                        "Error al eliminar el libro",
                        "Error",
                        JOptionPane.ERROR_MESSAGE);
            }
        }
    }

    private void mostrarDialogoNuevoPrestamo() {
        JOptionPane.showMessageDialog(this,
                "Nuevo préstamo\n(Funcionalidad en desarrollo)",
                "Información",
                JOptionPane.INFORMATION_MESSAGE);
    }
}