document.addEventListener("DOMContentLoaded", function () {
  const wishlistButtons = document.querySelectorAll(".wishlist-toggle");

  if (wishlistButtons.length === 0) {
    console.log("No se encontraron botones de wishlist.");
    return;
  }

  wishlistButtons.forEach(function (wishlistBtn) {
    const propertyId = wishlistBtn.getAttribute("data-id");
    const tooltip = wishlistBtn.querySelector(".tooltip-text");

    // Crear un portal para el tooltip y agregarlo al body
    const tooltipPortal = document.createElement("div");
    tooltipPortal.classList.add("tooltip-text-portal");
    document.body.appendChild(tooltipPortal);

    function updateTooltipText(isInWishlist) {
      tooltipPortal.textContent = isInWishlist
        ? "Eliminar del Wishlist"
        : "Agregar a Wishlist";
    }

    wishlistBtn.addEventListener("mouseenter", function () {
      const rect = wishlistBtn.getBoundingClientRect();
      tooltipPortal.style.visibility = "visible";
      tooltipPortal.style.opacity = "1";
      tooltipPortal.style.top = `${rect.top - tooltipPortal.offsetHeight}px`;
      tooltipPortal.style.left = `${rect.left + wishlistBtn.offsetWidth / 2 - tooltipPortal.offsetWidth / 2}px`;
    });

    wishlistBtn.addEventListener("mouseleave", function () {
      tooltipPortal.style.visibility = "hidden";
      tooltipPortal.style.opacity = "0";
    });

    wishlistBtn.addEventListener("click", function (event) {
      // Prevent the default action (if inside an <a> tag) and stop propagation to parent elements
      event.preventDefault(); // Prevents following the <a> link if clicked
      event.stopPropagation(); // Prevents the event from propagating up the DOM
    });

    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

    if (wishlist.includes(propertyId)) {
      wishlistBtn.classList.add("in-wishlist");
      updateTooltipText(true);
    } else {
      updateTooltipText(false);
    }

    wishlistBtn.addEventListener("click", function () {
      let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
      const isInWishlist = wishlist.includes(propertyId);

      if (isInWishlist) {
        wishlist = wishlist.filter((id) => id !== propertyId);
        wishlistBtn.classList.remove("in-wishlist");
      } else {
        wishlist.push(propertyId);
        wishlistBtn.classList.add("in-wishlist");
      }

      updateTooltipText(!isInWishlist);
      localStorage.setItem("wishlist", JSON.stringify(wishlist));
      updateWishlistOnServer(wishlist);
    });
  });

  // Función para sincronizar la lista de favoritos con el servidor y recargar la página en la página de favoritos
  function updateWishlistOnServer(wishlist) {
    fetch("/wp-admin/admin-ajax.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "action=set_wishlist_ids&wishlist_ids=" + JSON.stringify(wishlist),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Favoritos sincronizados con el servidor:", data);

        // Si estamos en la página de favoritos, recargar la página después de la sincronización
        if (window.location.pathname === "/favoritos/") {
          location.reload(); // Recargar la página para actualizar los ítems
        }
      })
      .catch((error) => {
        console.error("Error al sincronizar los favoritos:", error);
      });
  }
});
