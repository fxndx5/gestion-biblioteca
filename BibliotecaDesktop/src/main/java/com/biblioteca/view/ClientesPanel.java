package com.biblioteca.view;

import com.biblioteca.controller.ClienteController;
import com.biblioteca.model.Cliente;
import javax.swing.*;
import javax.swing.table.*;
import java.awt.*;
import java.util.List;

public class ClientesPanel extends JPanel {
    private final ClienteController controller;
    private JTable tablaClientes;
    private DefaultTableModel modeloTabla;

    // Colores consistentes con el Dashboard
    private final Color COLOR_PRIMARIO = new Color(25, 118, 210);
    private final Color COLOR_SECUNDARIO = new Color(13, 71, 161);
    private final Color COLOR_FONDO = new Color(248, 249, 250);
    private final Color COLOR_BORDE = new Color(230, 230, 230);

    public ClientesPanel() {
        controller = new ClienteController();
        setBackground(COLOR_FONDO);
        initComponents();
        cargarClientes();
    }

    private void initComponents() {
        setLayout(new BorderLayout());
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // Panel superior con título y botones
        JPanel topPanel = new JPanel(new BorderLayout());
        topPanel.setOpaque(false);

        JLabel lblTitulo = new JLabel("GESTIÓN DE CLIENTES");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblTitulo.setForeground(COLOR_SECUNDARIO);

        // Panel de búsqueda
        JPanel searchPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT));
        searchPanel.setOpaque(false);

        JTextField txtBuscar = new JTextField(15);
        txtBuscar.setFont(new Font("Segoe UI", Font.PLAIN, 13));
        txtBuscar.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(6, 10, 6, 10)
        ));

        JButton btnBuscar = new JButton("Buscar");
        btnBuscar.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        btnBuscar.setBackground(COLOR_PRIMARIO);
        btnBuscar.setForeground(Color.WHITE);
        btnBuscar.setBorder(BorderFactory.createLineBorder(COLOR_PRIMARIO.darker(), 1));
        btnBuscar.setFocusPainted(false);

        searchPanel.add(new JLabel("Buscar cliente:"));
        searchPanel.add(txtBuscar);
        searchPanel.add(btnBuscar);

        topPanel.add(lblTitulo, BorderLayout.WEST);
        topPanel.add(searchPanel, BorderLayout.EAST);

        // Panel de botones de acción
        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT, 8, 8));
        panelBotones.setOpaque(false);
        panelBotones.setBorder(BorderFactory.createEmptyBorder(15, 0, 15, 0));

        JButton btnNuevo = crearBotonConIcono("Nuevo Cliente", COLOR_PRIMARIO);
        JButton btnEditar = crearBotonConIcono("Editar", new Color(255, 152, 0));
        JButton btnEliminar = crearBotonConIcono("Eliminar", new Color(244, 67, 54));
        JButton btnSancionar = crearBotonConIcono("Sancionar/Quitar", new Color(156, 39, 176));
        JButton btnActualizar = crearBotonConIcono("Actualizar", new Color(56, 142, 60));

        panelBotones.add(btnNuevo);
        panelBotones.add(btnEditar);
        panelBotones.add(btnEliminar);
        panelBotones.add(btnSancionar);
        panelBotones.add(btnActualizar);

        // Tabla de clientes con estilo mejorado
        String[] columnas = {"ID", "DNI", "Nombre", "Email", "Teléfono", "Dirección", "Registro", "Sancionado"};
        modeloTabla = new DefaultTableModel(columnas, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }

            @Override
            public Class<?> getColumnClass(int columnIndex) {
                return Object.class;
            }
        };

        tablaClientes = new JTable(modeloTabla);

        // Configurar estilo de la tabla
        tablaClientes.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        tablaClientes.setRowHeight(30);
        tablaClientes.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        tablaClientes.getTableHeader().setBackground(COLOR_PRIMARIO);
        tablaClientes.getTableHeader().setForeground(Color.WHITE);
        tablaClientes.getTableHeader().setBorder(BorderFactory.createEmptyBorder());
        tablaClientes.setShowGrid(true);
        tablaClientes.setGridColor(new Color(240, 240, 240));
        tablaClientes.setSelectionBackground(new Color(220, 237, 255));
        tablaClientes.setSelectionForeground(Color.BLACK);

        // Personalizar renderizado de celdas
        tablaClientes.setDefaultRenderer(Object.class, new DefaultTableCellRenderer() {
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

                // Estilo especial para columna "Sancionado"
                if (column == 7) {
                    if ("SÍ".equals(value)) {
                        c.setForeground(new Color(220, 53, 69));
                    } else if ("NO".equals(value)) {
                        c.setForeground(new Color(40, 167, 69));
                    }
                }

                setHorizontalAlignment(SwingConstants.LEFT);

                return c;
            }
        });

        // Centrar contenido de algunas columnas
        DefaultTableCellRenderer centerRenderer = new DefaultTableCellRenderer();
        centerRenderer.setHorizontalAlignment(SwingConstants.CENTER);
        for (int i = 0; i < columnas.length; i++) {
            if (i == 0 || i == 1 || i == 7) { // ID, DNI, Sancionado
                tablaClientes.getColumnModel().getColumn(i).setCellRenderer(centerRenderer);
            }
        }

        // Ajustar anchos de columnas
        tablaClientes.getColumnModel().getColumn(0).setPreferredWidth(50);  // ID
        tablaClientes.getColumnModel().getColumn(1).setPreferredWidth(100); // DNI
        tablaClientes.getColumnModel().getColumn(2).setPreferredWidth(150); // Nombre
        tablaClientes.getColumnModel().getColumn(3).setPreferredWidth(150); // Email
        tablaClientes.getColumnModel().getColumn(4).setPreferredWidth(100); // Teléfono
        tablaClientes.getColumnModel().getColumn(5).setPreferredWidth(200); // Dirección
        tablaClientes.getColumnModel().getColumn(6).setPreferredWidth(100); // Registro
        tablaClientes.getColumnModel().getColumn(7).setPreferredWidth(80);  // Sancionado

        JScrollPane scrollPane = new JScrollPane(tablaClientes);
        scrollPane.setBorder(BorderFactory.createLineBorder(COLOR_BORDE, 1));
        scrollPane.getViewport().setBackground(Color.WHITE);

        // Acciones de botones
        btnNuevo.addActionListener(e -> mostrarDialogoNuevoCliente());
        btnEditar.addActionListener(e -> editarClienteSeleccionado());
        btnEliminar.addActionListener(e -> eliminarClienteSeleccionado());
        btnSancionar.addActionListener(e -> cambiarSancionCliente());
        btnActualizar.addActionListener(e -> cargarClientes());

        add(topPanel, BorderLayout.NORTH);
        add(panelBotones, BorderLayout.CENTER);
        add(scrollPane, BorderLayout.SOUTH);
    }

    private JButton crearBotonConIcono(String texto, Color color) {
        JButton boton = new JButton(texto);
        boton.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        boton.setBackground(color);
        boton.setForeground(Color.WHITE);
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

    private void cargarClientes() {
        modeloTabla.setRowCount(0);
        List<Cliente> clientes = controller.obtenerTodosClientes();

        for (Cliente cliente : clientes) {
            Object[] fila = {
                    cliente.getId(),
                    cliente.getDni(),
                    cliente.getNombre(),
                    cliente.getEmail(),
                    cliente.getTelefono(),
                    cliente.getDireccion(),
                    cliente.getFechaRegistro(),
                    cliente.isSancionado() ? "SÍ" : "NO"
            };
            modeloTabla.addRow(fila);
        }
    }

    private void mostrarDialogoNuevoCliente() {
        JDialog dialog = new JDialog((Frame) SwingUtilities.getWindowAncestor(this), "Nuevo Cliente", true);
        dialog.setSize(500, 450);
        dialog.setLocationRelativeTo(this);
        dialog.getContentPane().setBackground(Color.WHITE);

        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        panel.setBackground(Color.WHITE);

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Título
        JLabel lblTitulo = new JLabel("REGISTRAR NUEVO CLIENTE");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 16));
        lblTitulo.setForeground(COLOR_SECUNDARIO);
        lblTitulo.setHorizontalAlignment(SwingConstants.CENTER);

        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.gridwidth = 2;
        panel.add(lblTitulo, gbc);

        gbc.gridwidth = 1;
        gbc.anchor = GridBagConstraints.WEST;

        // Campos del formulario
        String[] labels = {"DNI:", "Nombre:", "Email:", "Teléfono:", "Dirección:"};
        JTextField[] fields = new JTextField[labels.length];

        for (int i = 0; i < labels.length; i++) {
            gbc.gridx = 0;
            gbc.gridy = i + 1;
            JLabel label = new JLabel(labels[i]);
            label.setFont(new Font("Segoe UI", Font.BOLD, 12));
            panel.add(label, gbc);

            gbc.gridx = 1;
            gbc.gridy = i + 1;
            fields[i] = new JTextField(20);
            fields[i].setFont(new Font("Segoe UI", Font.PLAIN, 13));
            fields[i].setBorder(BorderFactory.createCompoundBorder(
                    BorderFactory.createLineBorder(COLOR_BORDE, 1),
                    BorderFactory.createEmptyBorder(6, 8, 6, 8)
            ));
            panel.add(fields[i], gbc);
        }

        // Panel de botones
        gbc.gridx = 0;
        gbc.gridy = labels.length + 1;
        gbc.gridwidth = 2;
        gbc.fill = GridBagConstraints.NONE;
        gbc.anchor = GridBagConstraints.CENTER;

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.CENTER, 15, 0));
        panelBotones.setOpaque(false);

        JButton btnGuardar = new JButton("Guardar");
        btnGuardar.setFont(new Font("Segoe UI", Font.BOLD, 13));
        btnGuardar.setBackground(COLOR_PRIMARIO);
        btnGuardar.setForeground(Color.WHITE);
        btnGuardar.setBorder(BorderFactory.createLineBorder(COLOR_PRIMARIO.darker(), 1));
        btnGuardar.setFocusPainted(false);

        JButton btnCancelar = new JButton("Cancelar");
        btnCancelar.setFont(new Font("Segoe UI", Font.BOLD, 13));
        btnCancelar.setBackground(new Color(100, 100, 100));
        btnCancelar.setForeground(Color.WHITE);
        btnCancelar.setBorder(BorderFactory.createLineBorder(new Color(80, 80, 80), 1));
        btnCancelar.setFocusPainted(false);

        btnGuardar.addActionListener(e -> {
            Cliente nuevoCliente = new Cliente(
                    fields[0].getText(),
                    fields[1].getText(),
                    fields[2].getText(),
                    fields[3].getText(),
                    fields[4].getText()
            );

            if (controller.registrarCliente(nuevoCliente)) {
                JOptionPane.showMessageDialog(dialog, "Cliente registrado exitosamente");
                cargarClientes();
                dialog.dispose();
            } else {
                JOptionPane.showMessageDialog(dialog,
                        "Error al registrar cliente",
                        "Error",
                        JOptionPane.ERROR_MESSAGE);
            }
        });

        btnCancelar.addActionListener(e -> dialog.dispose());

        panelBotones.add(btnGuardar);
        panelBotones.add(btnCancelar);
        panel.add(panelBotones, gbc);

        dialog.setLayout(new BorderLayout());
        dialog.add(panel, BorderLayout.CENTER);
        dialog.setVisible(true);
    }

    private void editarClienteSeleccionado() {
        int filaSeleccionada = tablaClientes.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idCliente = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            Cliente cliente = controller.buscarClientePorId(idCliente);

            if (cliente != null) {
                JOptionPane.showMessageDialog(this,
                        "Editar cliente ID: " + idCliente + "\n(Funcionalidad en desarrollo)");
            }
        } else {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un cliente primero",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
        }
    }

    private void eliminarClienteSeleccionado() {
        int filaSeleccionada = tablaClientes.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idCliente = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            String nombre = (String) modeloTabla.getValueAt(filaSeleccionada, 2);

            int confirmacion = JOptionPane.showConfirmDialog(
                    this,
                    "¿Está seguro de eliminar este cliente?\n\n" +
                            "Nombre: " + nombre + "\n" +
                            "ID: " + idCliente,
                    "Confirmar eliminación",
                    JOptionPane.YES_NO_OPTION,
                    JOptionPane.WARNING_MESSAGE
            );

            if (confirmacion == JOptionPane.YES_OPTION) {
                if (controller.eliminarCliente(idCliente)) {
                    JOptionPane.showMessageDialog(this, "Cliente eliminado exitosamente");
                    cargarClientes();
                } else {
                    JOptionPane.showMessageDialog(this,
                            "Error al eliminar cliente",
                            "Error",
                            JOptionPane.ERROR_MESSAGE);
                }
            }
        } else {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un cliente primero",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
        }
    }

    private void cambiarSancionCliente() {
        int filaSeleccionada = tablaClientes.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idCliente = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            String estadoActual = (String) modeloTabla.getValueAt(filaSeleccionada, 7);
            String nombre = (String) modeloTabla.getValueAt(filaSeleccionada, 2);
            boolean sancionado = estadoActual.trim().equals("SÍ");

            String mensaje = sancionado ?
                    "¿Quitar sanción al cliente?\n\n" +
                            "Cliente: " + nombre :
                    "¿Sancionar al cliente?\n\n" +
                            "Cliente: " + nombre;

            int confirmacion = JOptionPane.showConfirmDialog(
                    this,
                    mensaje,
                    sancionado ? "Quitar Sanción" : "Aplicar Sanción",
                    JOptionPane.YES_NO_OPTION,
                    sancionado ? JOptionPane.QUESTION_MESSAGE : JOptionPane.WARNING_MESSAGE
            );

            if (confirmacion == JOptionPane.YES_OPTION) {
                if (controller.cambiarEstadoSancion(idCliente, !sancionado)) {
                    JOptionPane.showMessageDialog(this,
                            "Estado de sanción actualizado");
                    cargarClientes();
                } else {
                    JOptionPane.showMessageDialog(this,
                            "Error al actualizar sanción",
                            "Error",
                            JOptionPane.ERROR_MESSAGE);
                }
            }
        } else {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un cliente primero",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
        }
    }
}