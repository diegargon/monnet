<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="user-container">
    <h1>Crear Nuevo Usuario</h1>
    <form id="createUserForm">
        <table class="user-form-table">
            <tr>
                <td><label for="newUsername">Nombre de usuario:</label></td>
                <td><input type="text" id="newUsername" name="username" required></td>
            </tr>

            <tr>
                <td><label for="newEmail">Correo electrónico:</label></td>
                <td><input type="email" id="newEmail" name="email" required></td>
            </tr>

            <tr>
                <td><label for="userPassword">Contraseña:</label></td>
                <td>
                    <div class="password-container">
                        <input type="password" id="userPassword" name="password" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="confirmUserPassword">Confirmar contraseña:</label></td>
                <td>
                    <div class="password-container">
                        <input type="password" id="confirmUserPassword" name="confirmPassword" required>
                        <span class="toggle-password">👁️</span>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="userRole">Rol:</label></td>
                <td>
                    <div class="checkbox-container">
                        <input type="checkbox" id="isAdmin" name="isAdmin">
                        <label for="isAdmin">Usuario administrador</label>
                    </div>
                </td>
            </tr>
        </table>

        <div class="btn-container">
            <button disabled type="submit">Crear Usuario</button>
        </div>
    </form>
</div>
