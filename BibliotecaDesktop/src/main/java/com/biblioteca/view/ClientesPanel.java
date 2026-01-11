package com.biblioteca.view;

import com.biblioteca.controller.ClienteController;
import com.biblioteca.model.Cliente;
import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.util.List;

public class ClientesPanel extends JPanel {
    private final ClienteController controller;
    private JTable tablaClientes;
    private DefaultTableModel modeloTabla;

    public ClientesPanel() {
        controller = new ClienteController();
        initComponents();
        cargarClientes();
    }

    private void initComponents() {
        setLayout(new BorderLayout());

        // Panel superior con botones
        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.LEFT));

        JButton btnNuevo = new JButton("Nuevo Cliente");
        JButton btnEditar = new JButton("Editar");
        JButton btnEliminar = new JButton("Eliminar");
        JButton btnSancionar = new JButton("Sancionar/Quitar Sanción");
        JButton btnActualizar = new JButton("Actualizar");

        btnNuevo.addActionListener(e -> mostrarDialogoNuevoCliente());
        btnEditar.addActionListener(e -> editarClienteSeleccionado());
        btnEliminar.addActionListener(e -> eliminarClienteSeleccionado());
        btnSancionar.addActionListener(e -> cambiarSancionCliente());
        btnActualizar.addActionListener(e -> cargarClientes());

        panelBotones.add(btnNuevo);
        panelBotones.add(btnEditar);
        panelBotones.add(btnEliminar);
        panelBotones.add(btnSancionar);
        panelBotones.add(btnActualizar);

        // Tabla de clientes
        String[] columnas = {"ID", "DNI", "Nombre", "Email", "Teléfono", "Dirección", "Registro", "Sancionado"};
        modeloTabla = new DefaultTableModel(columnas, 0) {
            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }
        };

        tablaClientes = new JTable(modeloTabla);
        JScrollPane scrollPane = new JScrollPane(tablaClientes);

        add(panelBotones, BorderLayout.NORTH);
        add(scrollPane, BorderLayout.CENTER);
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
        dialog.setSize(400, 400);
        dialog.setLocationRelativeTo(this);

        JPanel panel = new JPanel(new GridLayout(6, 2, 10, 10));

        panel.add(new JLabel("DNI:"));
        JTextField txtDni = new JTextField();
        panel.add(txtDni);

        panel.add(new JLabel("Nombre:"));
        JTextField txtNombre = new JTextField();
        panel.add(txtNombre);

        panel.add(new JLabel("Email:"));
        JTextField txtEmail = new JTextField();
        panel.add(txtEmail);

        panel.add(new JLabel("Teléfono:"));
        JTextField txtTelefono = new JTextField();
        panel.add(txtTelefono);

        panel.add(new JLabel("Dirección:"));
        JTextField txtDireccion = new JTextField();
        panel.add(txtDireccion);

        JButton btnGuardar = new JButton("Guardar");
        JButton btnCancelar = new JButton("Cancelar");

        btnGuardar.addActionListener(e -> {
            Cliente nuevoCliente = new Cliente(
                    txtDni.getText(),
                    txtNombre.getText(),
                    txtEmail.getText(),
                    txtTelefono.getText(),
                    txtDireccion.getText()
            );

            if (controller.registrarCliente(nuevoCliente)) {
                JOptionPane.showMessageDialog(dialog, "Cliente registrado exitosamente");
                cargarClientes();
                dialog.dispose();
            } else {
                JOptionPane.showMessageDialog(dialog, "Error al registrar cliente", "Error", JOptionPane.ERROR_MESSAGE);
            }
        });

        btnCancelar.addActionListener(e -> dialog.dispose());

        JPanel panelBotones = new JPanel();
        panelBotones.add(btnGuardar);
        panelBotones.add(btnCancelar);

        dialog.setLayout(new BorderLayout());
        dialog.add(panel, BorderLayout.CENTER);
        dialog.add(panelBotones, BorderLayout.SOUTH);

        dialog.setVisible(true);
    }

    private void editarClienteSeleccionado() {
        int filaSeleccionada = tablaClientes.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idCliente = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            Cliente cliente = controller.buscarClientePorId(idCliente);

            if (cliente != null) {
                // Mostrar diálogo de edición similar al de nuevo cliente
                JOptionPane.showMessageDialog(this, "Funcionalidad de edición pendiente");
            }
        } else {
            JOptionPane.showMessageDialog(this, "Seleccione un cliente primero", "Advertencia", JOptionPane.WARNING_MESSAGE);
        }
    }

    private void eliminarClienteSeleccionado() {
        int filaSeleccionada = tablaClientes.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idCliente = (int) modeloTabla.getValueAt(filaSeleccionada, 0);

            int confirmacion = JOptionPane.showConfirmDialog(
                    this,
                    "¿Está seguro de eliminar este cliente?",
                    "Confirmar eliminación",
                    JOptionPane.YES_NO_OPTION
            );

            if (confirmacion == JOptionPane.YES_OPTION) {
                if (controller.eliminarCliente(idCliente)) {
                    JOptionPane.showMessageDialog(this, "Cliente eliminado exitosamente");
                    cargarClientes();
                } else {
                    JOptionPane.showMessageDialog(this, "Error al eliminar cliente", "Error", JOptionPane.ERROR_MESSAGE);
                }
            }
        } else {
            JOptionPane.showMessageDialog(this, "Seleccione un cliente primero", "Advertencia", JOptionPane.WARNING_MESSAGE);
        }
    }

    private void cambiarSancionCliente() {
        int filaSeleccionada = tablaClientes.getSelectedRow();
        if (filaSeleccionada >= 0) {
            int idCliente = (int) modeloTabla.getValueAt(filaSeleccionada, 0);
            String estadoActual = (String) modeloTabla.getValueAt(filaSeleccionada, 7);
            boolean sancionado = estadoActual.equals("SÍ");

            String mensaje = sancionado ?
                    "¿Quitar sanción al cliente?" :
                    "¿Sancionar al cliente?";

            int confirmacion = JOptionPane.showConfirmDialog(
                    this,
                    mensaje,
                    "Confirmar",
                    JOptionPane.YES_NO_OPTION
            );

            if (confirmacion == JOptionPane.YES_OPTION) {
                if (controller.cambiarEstadoSancion(idCliente, !sancionado)) {
                    JOptionPane.showMessageDialog(this, "Estado de sanción actualizado");
                    cargarClientes();
                } else {
                    JOptionPane.showMessageDialog(this, "Error al actualizar sanción", "Error", JOptionPane.ERROR_MESSAGE);
                }
            }
        } else {
            JOptionPane.showMessageDialog(this, "Seleccione un cliente primero", "Advertencia", JOptionPane.WARNING_MESSAGE);
        }
    }
}