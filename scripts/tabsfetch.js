/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

// Usamos un MutationObserver para detectar cuando se inserta un div con la clase .host-details
const observer = new MutationObserver(() => {
    const tabButtons = document.querySelectorAll(".host-details-tabs-head");

    tabButtons.forEach(button => {
        // Comprobar si el evento ya ha sido asignado al botón
        if (!button.hasAttribute("data-clicked")) {
            button.setAttribute("data-clicked", "true"); // Marca el botón como procesado

            button.addEventListener("click", async () => {
                const tabId = button.getAttribute("data-tab");
                //const contentDiv = document.getElementById("content");

                try {
                    const response = await fetch("submitter.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            command: "load_tab_data",
                            tabId: tabId
                        })
                    });

                    console.log(tabId);

                    if (!response.ok) throw new Error("Error en la solicitud");

                    const data = await response.json();
                    //contentDiv.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                } catch (error) {
                    //contentDiv.innerHTML = "Error al cargar los datos.";
                }
            });
        }
    });
});

// Iniciamos el observer para detectar cambios en el body (puedes especificar un contenedor más específico)
observer.observe(document.body, { childList: true, subtree: true });
