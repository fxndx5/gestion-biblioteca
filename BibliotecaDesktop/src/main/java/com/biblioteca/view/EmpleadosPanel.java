package com.biblioteca.view;

import com.biblioteca.controller.EmpleadoController;
import com.biblioteca.model.Empleado;
import javax.swing.*;
import javax.swing.table.*;
import java.awt.*;
import java.util.List;

public class EmpleadosPanel extends JPanel {
    private final EmpleadoController controller;
    private JTable tablaEmpleados;
    private DefaultTableModel modeloTabla;

    // Colores consistentes con el Dashboard
    private final Color COLOR_PRIMARIO = new Color(25, 118, 210);
    private final Color COLOR_SECUNDARIO = new Color(13, 71, 161);
    private final Color COLOR_FONDO = new Color(248, 249, 250);
    private final Color COLOR_BORDE = new Color(230, 230, 230);

    public EmpleadosPanel() {
        controller = new EmpleadoController();
        setBackground(COLOR_FONDO);
        initComponents();
        cargarEmpleados();
    }

    private void initComponents() {
        setLayout(new BorderLayout());
        setBorder(BorderFactory.createEmptyBorder(15, 15, 15, 15));

        // Panel superior con título
        JPanel topPanel = new JPanel(new BorderLayout());
        topPanel.setOpaque(false);

        JLabel lblTitulo = new JLabel("GESTIÓN DE EMPLEADOS");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblTitulo.setForeground(COLOR_SECUNDARIO);

        topPanel.add(lblTitulo, BorderLayout.WEST);

        // Panel de botones de acción
        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT, 8, 8));
        panelBotones.setOpaque(false);
        panelBotones.setBorder(BorderFactory.createEmptyBorder(15, 0, 15, 0));

        JButton btnNuevo = crearBotonConIcono("Nuevo Empleado", COLOR_PRIMARIO);
        JButton btnEditar = crearBotonConIcono("Editar", COLOR_PRIMARIO);
        JButton btnEliminar = crearBotonConIcono("Eliminar", COLOR_PRIMARIO);
        JButton btnActivarDesactivar = crearBotonConIcono("Activar/Desactivar", COLOR_PRIMARIO);


        panelBotones.add(btnNuevo);
        panelBotones.add(btnEditar);
        panelBotones.add(btnEliminar);
        panelBotones.add(btnActivarDesactivar);


        // Tabla de empleados con estilo mejorado
        String[] columnas = {"ID", "DNI", "Nombre", "Email", "Teléfono", "Cargo", "Usuario", "Registro", "Estado"};
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

        tablaEmpleados = new JTable(modeloTabla);

        // Configurar estilo de la tabla
        tablaEmpleados.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        tablaEmpleados.setRowHeight(30);
        tablaEmpleados.getTableHeader().setFont(new Font("Segoe UI", Font.BOLD, 12));
        tablaEmpleados.getTableHeader().setBackground(COLOR_PRIMARIO);
        tablaEmpleados.getTableHeader().setForeground(Color.BLACK);
        tablaEmpleados.getTableHeader().setBorder(BorderFactory.createEmptyBorder());
        tablaEmpleados.setShowGrid(true);
        tablaEmpleados.setGridColor(new Color(240, 240, 240));
        tablaEmpleados.setSelectionBackground(new Color(220, 237, 255));
        tablaEmpleados.setSelectionForeground(Color.BLACK);

        // Personalizar renderizado de celdas
        tablaEmpleados.setDefaultRenderer(Object.class, new DefaultTableCellRenderer() {
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

                // Estilo especial para columna "Estado"
                if (column == 8) {
                    if ("Activo".equals(value)) {
                        c.setForeground(new Color(40, 167, 69));
                    } else if ("Inactivo".equals(value)) {
                        c.setForeground(new Color(220, 53, 69));
                    }
                }

                // Centrar algunas columnas
                if (column == 0 || column == 1 || column == 8) {
                    setHorizontalAlignment(SwingConstants.CENTER);
                } else {
                    setHorizontalAlignment(SwingConstants.LEFT);
                }

                return c;
            }
        });

        // Ajustar anchos de columnas
        tablaEmpleados.getColumnModel().getColumn(0).setPreferredWidth(50);  // ID
        tablaEmpleados.getColumnModel().getColumn(1).setPreferredWidth(100); // DNI
        tablaEmpleados.getColumnModel().getColumn(2).setPreferredWidth(150); // Nombre
        tablaEmpleados.getColumnModel().getColumn(3).setPreferredWidth(150); // Email
        tablaEmpleados.getColumnModel().getColumn(4).setPreferredWidth(100); // Teléfono
        tablaEmpleados.getColumnModel().getColumn(5).setPreferredWidth(100); // Cargo
        tablaEmpleados.getColumnModel().getColumn(6).setPreferredWidth(100); // Usuario
        tablaEmpleados.getColumnModel().getColumn(7).setPreferredWidth(100); // Registro
        tablaEmpleados.getColumnModel().getColumn(8).setPreferredWidth(80);  // Estado

        JScrollPane scrollPane = new JScrollPane(tablaEmpleados);
        scrollPane.setBorder(BorderFactory.createLineBorder(COLOR_BORDE, 1));
        scrollPane.getViewport().setBackground(Color.WHITE);

        // Acciones de botones
        btnNuevo.addActionListener(e -> mostrarDialogoNuevoEmpleado());
        btnEditar.addActionListener(e -> editarEmpleadoSeleccionado());
        btnEliminar.addActionListener(e -> eliminarEmpleadoSeleccionado());
        btnActivarDesactivar.addActionListener(e -> cambiarEstadoEmpleado());

        add(topPanel, BorderLayout.NORTH);
        add(panelBotones, BorderLayout.CENTER);
        add(scrollPane, BorderLayout.SOUTH);
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

    private void cargarEmpleados() {
        modeloTabla.setRowCount(0);
        List<Empleado> empleados = controller.obtenerTodosEmpleados();

        for (Empleado empleado : empleados) {
            Object[] fila = {
                    empleado.getId(),
                    empleado.getDni(),
                    empleado.getNombre(),
                    empleado.getEmail(),
                    empleado.getTelefono(),
                    empleado.getCargo(),
                    empleado.getUsuario(),
                    empleado.getFechaRegistro(),
                    empleado.isActivo() ? "Activo" : "Inactivo"
            };
            modeloTabla.addRow(fila);
        }
    }

    private void mostrarDialogoNuevoEmpleado() {
        JDialog dialog = new JDialog((Frame) SwingUtilities.getWindowAncestor(this), "Nuevo Empleado", true);
        dialog.setSize(500, 550);
        dialog.setLocationRelativeTo(this);
        dialog.getContentPane().setBackground(Color.WHITE);

        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        panel.setBackground(Color.WHITE);

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Título
        JLabel lblTitulo = new JLabel("REGISTRAR NUEVO EMPLEADO");
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
        String[] labels = {"DNI:", "Nombre:", "Email:", "Teléfono:", "Cargo:", "Usuario:", "Contraseña:"};
        JTextField[] fields = new JTextField[labels.length];
        fields[6] = new JPasswordField(20); // Para la contraseña

        for (int i = 0; i < labels.length; i++) {
            gbc.gridx = 0;
            gbc.gridy = i + 1;
            JLabel label = new JLabel(labels[i]);
            label.setFont(new Font("Segoe UI", Font.BOLD, 12));
            panel.add(label, gbc);

            gbc.gridx = 1;
            gbc.gridy = i + 1;
            if (i == 6) {
                // Campo de contraseña
                panel.add(fields[i], gbc);
            } else {
                fields[i] = new JTextField(20);
                fields[i].setFont(new Font("Segoe UI", Font.PLAIN, 13));
                fields[i].setBorder(BorderFactory.createCompoundBorder(
                        BorderFactory.createLineBorder(COLOR_BORDE, 1),
                        BorderFactory.createEmptyBorder(6, 8, 6, 8)
                ));
                panel.add(fields[i], gbc);
            }
        }

        // Checkbox para activo
        gbc.gridx = 0;
        gbc.gridy = labels.length + 1;
        JLabel lblActivo = new JLabel("Activo:");
        lblActivo.setFont(new Font("Segoe UI", Font.BOLD, 12));
        panel.add(lblActivo, gbc);

        gbc.gridx = 1;
        gbc.gridy = labels.length + 1;
        JCheckBox chkActivo = new JCheckBox();
        chkActivo.setSelected(true);
        panel.add(chkActivo, gbc);

        // Panel de botones
        gbc.gridx = 0;
        gbc.gridy = labels.length + 2;
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
        btnCancelar.setForeground(Color.WHITE);
        btnCancelar.setBorder(BorderFactory.createLineBorder(new Color(80, 80, 80), 1));
        btnCancelar.setFocusPainted(false);

        btnGuardar.addActionListener(e -> {
            // Validar campos obligatorios
            if (fields[0].getText().isEmpty() || fields[1].getText().isEmpty() ||
                    fields[5].getText().isEmpty() || fields[6].getText().isEmpty()) {
                JOptionPane.showMessageDialog(dialog,
                        "Por favor complete todos los campos obligatorios",
                        "Error",
                        JOptionPane.ERROR_MESSAGE);
                return;
            }

            Empleado nuevoEmpleado = new Empleado();
            nuevoEmpleado.setDni(fields[0].getText());
            nuevoEmpleado.setNombre(fields[1].getText());
            nuevoEmpleado.setEmail(fields[2].getText());
            nuevoEmpleado.setTelefono(fields[3].getText());
            nuevoEmpleado.setCargo(fields[4].getText());
            nuevoEmpleado.setUsuario(fields[5].getText());
            nuevoEmpleado.setPassword(fields[6].getText()); // Encriptar en el DAO
            nuevoEmpleado.setActivo(chkActivo.isSelected());
            nuevoEmpleado.setFechaRegistro(java.time.LocalDate.now().toString());

            if (controller.agregarEmpleado(nuevoEmpleado)) {
                JOptionPane.showMessageDialog(dialog, "Empleado registrado exitosamente");
                cargarEmpleados();
                dialog.dispose();
            } else {
                JOptionPane.showMessageDialog(dialog,
                        "Error al registrar empleado. Verifique que el DNI o usuario no existan.",
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

    private void editarEmpleadoSeleccionado() {
        int filaSeleccionada = tablaEmpleados.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idEmpleado = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            Empleado empleado = controller.buscarEmpleadoPorId(idEmpleado);

            if (empleado != null) {
                // Similar a mostrarDialogoNuevoEmpleado pero con datos precargados
                JOptionPane.showMessageDialog(this,
                        "Editar empleado ID: " + idEmpleado + "\n(Funcionalidad en desarrollo)");
            }
        } else {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un empleado primero",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
        }
    }

    private void eliminarEmpleadoSeleccionado() {
        int filaSeleccionada = tablaEmpleados.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idEmpleado = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            String nombre = (String) modeloTabla.getValueAt(filaSeleccionada, 2);

            int confirmacion = JOptionPane.showConfirmDialog(
                    this,
                    "¿Está seguro de eliminar este empleado?\n\n" +
                            "Nombre: " + nombre + "\n" +
                            "ID: " + idEmpleado,
                    "Confirmar eliminación",
                    JOptionPane.YES_NO_OPTION,
                    JOptionPane.WARNING_MESSAGE
            );

            if (confirmacion == JOptionPane.YES_OPTION) {
                if (controller.eliminarEmpleado(idEmpleado)) {
                    JOptionPane.showMessageDialog(this, "Empleado eliminado exitosamente");
                    cargarEmpleados();
                } else {
                    JOptionPane.showMessageDialog(this,
                            "Error al eliminar empleado",
                            "Error",
                            JOptionPane.ERROR_MESSAGE);
                }
            }
        } else {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un empleado primero",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
        }
    }

    private void cambiarEstadoEmpleado() {
        int filaSeleccionada = tablaEmpleados.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idEmpleado = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            String estadoActual = (String) modeloTabla.getValueAt(filaSeleccionada, 8);
            String nombre = (String) modeloTabla.getValueAt(filaSeleccionada, 2);
            boolean activo = "Activo".equals(estadoActual);

            String mensaje = activo ?
                    "¿Desactivar al empleado?\n\n" +
                            "Empleado: " + nombre :
                    "¿Activar al empleado?\n\n" +
                            "Empleado: " + nombre;

            int confirmacion = JOptionPane.showConfirmDialog(
                    this,
                    mensaje,
                    activo ? "Desactivar Empleado" : "Activar Empleado",
                    JOptionPane.YES_NO_OPTION,
                    activo ? JOptionPane.WARNING_MESSAGE : JOptionPane.QUESTION_MESSAGE
            );

            if (confirmacion == JOptionPane.YES_OPTION) {
                if (controller.cambiarEstadoEmpleado(idEmpleado, !activo)) {
                    JOptionPane.showMessageDialog(this,
                            "Estado del empleado actualizado");
                    cargarEmpleados();
                } else {
                    JOptionPane.showMessageDialog(this,
                            "Error al actualizar estado",
                            "Error",
                            JOptionPane.ERROR_MESSAGE);
                }
            }
        } else {
            JOptionPane.showMessageDialog(this,
                    "Seleccione un empleado primero",
                    "Advertencia",
                    JOptionPane.WARNING_MESSAGE);
        }
    }
}
