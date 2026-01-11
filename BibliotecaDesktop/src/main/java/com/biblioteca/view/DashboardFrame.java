package com.biblioteca.view;

import com.biblioteca.controller.LibroController;
import com.biblioteca.controller.PrestamoController;
import com.biblioteca.controller.ClienteController;
import com.biblioteca.model.Libro;
import com.biblioteca.model.Prestamo;
import com.biblioteca.model.Cliente;
import javax.swing.*;
import javax.swing.table.DefaultTableModel;
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
    private JTable tablaClientes;
    private DefaultTableModel modelLibros;
    private DefaultTableModel modelPrestamos;
    private DefaultTableModel modelClientes;

    private LibroController libroController;
    private PrestamoController prestamoController;
    private ClienteController clienteController;

    public DashboardFrame() {
        setTitle("Dashboard - Gestión de Biblioteca");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setSize(1200, 800);
        setLocationRelativeTo(null);

        // Inicializar controladores
        libroController = new LibroController();
        prestamoController = new PrestamoController();
        clienteController = new ClienteController();

        initComponents();
        cargarDatosInicio();
    }

    private void initComponents() {
        // Crear barra de menú
        JMenuBar menuBar = new JMenuBar();

        JMenu menuArchivo = new JMenu("Archivo");
        JMenuItem itemSalir = new JMenuItem("Salir");
        itemSalir.addActionListener(e -> System.exit(0));
        menuArchivo.add(itemSalir);

        JMenu menuAyuda = new JMenu("Ayuda");
        JMenuItem itemAcerca = new JMenuItem("Acerca de");
        menuAyuda.add(itemAcerca);

        menuBar.add(menuArchivo);
        menuBar.add(menuAyuda);
        setJMenuBar(menuBar);

        // Crear pestañas
        tabbedPane = new JTabbedPane();

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

        add(tabbedPane, BorderLayout.CENTER);

        cargarLibros();
        cargarPrestamos();
        cargarClientes();
    }

    private JPanel crearPanelInicio() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        JLabel lblTitulo = new JLabel("ESTADÍSTICAS GENERALES", SwingConstants.CENTER);
        lblTitulo.setFont(new Font("Arial", Font.BOLD, 24));
        lblTitulo.setForeground(new Color(44, 62, 80));

        JPanel panelStats = new JPanel(new GridLayout(2, 2, 20, 20));

        int totalLibros = libroController.obtenerTodosLosLibros().size();
        int totalClientes = clienteController.obtenerTodosClientes().size();

        panelStats.add(crearTarjetaEstadistica("Total Libros", String.valueOf(totalLibros), Color.BLUE));
        panelStats.add(crearTarjetaEstadistica("Libros Prestados", "0", Color.ORANGE));
        panelStats.add(crearTarjetaEstadistica("Clientes Registrados", String.valueOf(totalClientes), Color.GREEN));
        panelStats.add(crearTarjetaEstadistica("Préstamos Activos", "0", Color.RED));

        panel.add(lblTitulo, BorderLayout.NORTH);
        panel.add(panelStats, BorderLayout.CENTER);

        return panel;
    }

    private JPanel crearTarjetaEstadistica(String titulo, String valor, Color color) {
        JPanel tarjeta = new JPanel(new BorderLayout());
        tarjeta.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(color, 2),
                BorderFactory.createEmptyBorder(20, 20, 20, 20)
        ));
        tarjeta.setBackground(Color.WHITE);

        JLabel lblTitulo = new JLabel(titulo, SwingConstants.CENTER);
        lblTitulo.setFont(new Font("Arial", Font.BOLD, 16));
        lblTitulo.setForeground(Color.DARK_GRAY);

        JLabel lblValor = new JLabel(valor, SwingConstants.CENTER);
        lblValor.setFont(new Font("Arial", Font.BOLD, 36));
        lblValor.setForeground(color);

        tarjeta.add(lblTitulo, BorderLayout.NORTH);
        tarjeta.add(lblValor, BorderLayout.CENTER);

        return tarjeta;
    }

    private JPanel crearPanelLibros() {
        JPanel panel = new JPanel(new BorderLayout());

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT));

        JButton btnNuevo = new JButton("➕ Nuevo Libro");
        JButton btnEditar = new JButton("✏️ Editar");
        JButton btnEliminar = new JButton("🗑️ Eliminar");
        JButton btnActualizar = new JButton("🔄 Actualizar");

        panelBotones.add(btnNuevo);
        panelBotones.add(btnEditar);
        panelBotones.add(btnEliminar);
        panelBotones.add(btnActualizar);

        String[] columnas = {"ID", "Título", "Autor", "ISBN", "Categoría", "Ejemplares", "Disponibles", "Ubicación"};
        modelLibros = new DefaultTableModel(columnas, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false; // Hacer la tabla no editable
            }
        };
        tablaLibros = new JTable(modelLibros);
        JScrollPane scrollPane = new JScrollPane(tablaLibros);

        btnActualizar.addActionListener(e -> cargarLibros());
        btnNuevo.addActionListener(e -> mostrarDialogoNuevoLibro());
        btnEditar.addActionListener(e -> editarLibroSeleccionado());
        btnEliminar.addActionListener(e -> eliminarLibroSeleccionado());

        panel.add(panelBotones, BorderLayout.NORTH);
        panel.add(scrollPane, BorderLayout.CENTER);

        return panel;
    }

    private JPanel crearPanelPrestamos() {
        JPanel panel = new JPanel(new BorderLayout());

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT));

        JButton btnNuevoPrestamo = new JButton("Nuevo Préstamo");
        JButton btnDevolver = new JButton("↩Registrar Devolución");
        JButton btnActualizar = new JButton("ctualizar");

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
        JScrollPane scrollPane = new JScrollPane(tablaPrestamos);

        btnActualizar.addActionListener(e -> cargarPrestamos());
        btnNuevoPrestamo.addActionListener(e -> mostrarDialogoNuevoPrestamo());

        panel.add(panelBotones, BorderLayout.NORTH);
        panel.add(scrollPane, BorderLayout.CENTER);

        return panel;
    }

    private JPanel crearPanelClientes() {
        JPanel panel = new JPanel(new BorderLayout());

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT));

        JButton btnNuevoCliente = new JButton("Nuevo Cliente");
        JButton btnActualizar = new JButton("Actualizar");

        panelBotones.add(btnNuevoCliente);
        panelBotones.add(btnActualizar);

        String[] columnas = {"ID", "DNI", "Nombre", "Email", "Teléfono", "Registro", "Sancionado"};
        modelClientes = new DefaultTableModel(columnas, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }
        };
        tablaClientes = new JTable(modelClientes);
        JScrollPane scrollPane = new JScrollPane(tablaClientes);

        btnActualizar.addActionListener(e -> cargarClientes());
        btnNuevoCliente.addActionListener(e -> mostrarDialogoNuevoCliente());

        panel.add(panelBotones, BorderLayout.NORTH);
        panel.add(scrollPane, BorderLayout.CENTER);

        return panel;
    }

    private JPanel crearPanelCategorias() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        JLabel lblInfo = new JLabel(
                "<html><center><h2>Gestión de Categorías</h2>" +
                        "<p>Desde aquí puedes gestionar las categorías de libros.</p>" +
                        "<p>Ejemplos: Novela, Ciencia, Historia, Infantil, etc.</p></center></html>",
                SwingConstants.CENTER
        );

        panel.add(lblInfo, BorderLayout.CENTER);

        return panel;
    }

    private void cargarDatosInicio() {
        // Si queiro cargar mas datos de libros
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
        Object[] row = {1, "El Quijote", "Juan Pérez", "2024-01-15", "2024-02-15", "Activo"};
        modelPrestamos.addRow(row);
    }

    private void cargarClientes() {
        modelClientes.setRowCount(0);
        Object[] row = {1, "12345678A", "Juan Pérez", "juan@email.com", "600123456", "2024-01-01", "No"};
        modelClientes.addRow(row);
    }

    private void mostrarDialogoNuevoLibro() {
        JDialog dialog = new JDialog(this, "Nuevo Libro", true);
        dialog.setSize(500, 500);
        dialog.setLocationRelativeTo(this);

        JPanel panel = new JPanel(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(5, 5, 5, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        gbc.gridx = 0; gbc.gridy = 0;
        panel.add(new JLabel("Título:"), gbc);

        gbc.gridx = 1; gbc.gridy = 0;
        JTextField txtTitulo = new JTextField(20);
        panel.add(txtTitulo, gbc);

        gbc.gridx = 0; gbc.gridy = 1;
        panel.add(new JLabel("Autor:"), gbc);

        gbc.gridx = 1; gbc.gridy = 1;
        JTextField txtAutor = new JTextField(20);
        panel.add(txtAutor, gbc);

        gbc.gridx = 0; gbc.gridy = 2;
        panel.add(new JLabel("ISBN:"), gbc);

        gbc.gridx = 1; gbc.gridy = 2;
        JTextField txtIsbn = new JTextField(20);
        panel.add(txtIsbn, gbc);

        gbc.gridx = 0; gbc.gridy = 3;
        panel.add(new JLabel("Ejemplares:"), gbc);

        gbc.gridx = 1; gbc.gridy = 3;
        JSpinner spnEjemplares = new JSpinner(new SpinnerNumberModel(1, 1, 100, 1));
        panel.add(spnEjemplares, gbc);

        gbc.gridx = 0; gbc.gridy = 4;
        gbc.gridwidth = 2;
        gbc.fill = GridBagConstraints.NONE;
        JPanel panelBotones = new JPanel();

        JButton btnGuardar = new JButton("Guardar");
        JButton btnCancelar = new JButton("Cancelar");

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
            JOptionPane.showMessageDialog(this, "Seleccione un libro para editar", "Advertencia", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int id = (int) modelLibros.getValueAt(selectedRow, 0);
        Libro libro = libroController.buscarLibroPorId(id);

        if (libro != null) {
            JOptionPane.showMessageDialog(this, "Editar libro ID: " + id + "\n(Funcionalidad en desarrollo)");
        }
    }

    private void eliminarLibroSeleccionado() {
        int selectedRow = tablaLibros.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Seleccione un libro para eliminar", "Advertencia", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int id = (int) modelLibros.getValueAt(selectedRow, 0);
        String titulo = (String) modelLibros.getValueAt(selectedRow, 1);

        int confirm = JOptionPane.showConfirmDialog(this,
                "¿Está seguro de eliminar el libro:\n" + titulo + "?",
                "Confirmar eliminación",
                JOptionPane.YES_NO_OPTION);

        if (confirm == JOptionPane.YES_OPTION) {
            if (libroController.eliminarLibro(id)) {
                JOptionPane.showMessageDialog(this, "Libro eliminado exitosamente");
                cargarLibros();
            } else {
                JOptionPane.showMessageDialog(this, "Error al eliminar el libro", "Error", JOptionPane.ERROR_MESSAGE);
            }
        }
    }

    private void mostrarDialogoNuevoPrestamo() {
        JOptionPane.showMessageDialog(this, "Nuevo préstamo\n(Funcionalidad en desarrollo)");
    }

    private void mostrarDialogoNuevoCliente() {
        JOptionPane.showMessageDialog(this, "Nuevo cliente\n(Funcionalidad en desarrollo)");
    }
}