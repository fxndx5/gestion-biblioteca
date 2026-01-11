package com.biblioteca.view;

import com.biblioteca.controller.LoginController;
import javax.swing.*;
import java.awt.*;

public class LoginFrame extends JFrame {
    private LoginController controller;
    private JTextField txtUsuario;
    private JPasswordField txtPassword;
    private JLabel lblMensaje;

    public LoginFrame() {
        setTitle("Login - Sistema de Biblioteca");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setSize(400, 300);
        setLocationRelativeTo(null);
        setResizable(false);

        controller = new LoginController();
        initComponents();
    }

    private void initComponents() {
        JPanel mainPanel = new JPanel(new BorderLayout());
        mainPanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        // Panel de título
        JPanel panelTitulo = new JPanel();
        JLabel lblTitulo = new JLabel("SISTEMA DE BIBLIOTECA");
        lblTitulo.setFont(new Font("Arial", Font.BOLD, 18));
        lblTitulo.setForeground(new Color(44, 62, 80));
        panelTitulo.add(lblTitulo);

        // Panel del formulario
        JPanel panelForm = new JPanel(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(10, 10, 10, 10);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Usuario
        gbc.gridx = 0;
        gbc.gridy = 0;
        panelForm.add(new JLabel("Usuario:"), gbc);

        gbc.gridx = 1;
        gbc.gridy = 0;
        gbc.gridwidth = 2;
        txtUsuario = new JTextField(15);
        panelForm.add(txtUsuario, gbc);

        // Contraseña
        gbc.gridx = 0;
        gbc.gridy = 1;
        gbc.gridwidth = 1;
        panelForm.add(new JLabel("Contraseña:"), gbc);

        gbc.gridx = 1;
        gbc.gridy = 1;
        gbc.gridwidth = 2;
        txtPassword = new JPasswordField(15);
        panelForm.add(txtPassword, gbc);

        // Botón Login
        gbc.gridx = 1;
        gbc.gridy = 2;
        gbc.gridwidth = 1;
        JButton btnLogin = new JButton("Iniciar Sesión");
        btnLogin.setBackground(new Color(52, 152, 219));
        btnLogin.setForeground(Color.WHITE);
        btnLogin.setFocusPainted(false);

        btnLogin.addActionListener(e -> {
            String usuario = txtUsuario.getText();
            String password = new String(txtPassword.getPassword());

            if (controller.autenticar(usuario, password)) {
                DashboardFrame dashboard = new DashboardFrame();
                dashboard.setVisible(true);
                this.dispose();
            } else {
                JOptionPane.showMessageDialog(this,
                        "Usuario o contraseña incorrectos",
                        "Error de autenticación",
                        JOptionPane.ERROR_MESSAGE);
            }
        });

        panelForm.add(btnLogin, gbc);

        // Mensaje de error
        lblMensaje = new JLabel(" ", SwingConstants.CENTER);
        lblMensaje.setForeground(Color.RED);

        // Permitir login con Enter
        txtPassword.addActionListener(e -> btnLogin.doClick());

        // Agregar componentes al panel principal
        mainPanel.add(panelTitulo, BorderLayout.NORTH);
        mainPanel.add(panelForm, BorderLayout.CENTER);
        mainPanel.add(lblMensaje, BorderLayout.SOUTH);

        add(mainPanel);
    }
}