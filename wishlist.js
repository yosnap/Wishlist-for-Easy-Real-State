document.addEventListener("DOMContentLoaded", function () {
  const wishlistButtons = document.querySelectorAll(".wishlist-toggle");

  if (wishlistButtons.length === 0) {
    console.log("No se encontraron botones de wishlist.");
    return;
  }

  wishlistButtons.forEach(function (wishlistBtn) {
    const propertyId = wishlistBtn.getAttribute("data-id");
    const tooltip = wishlistBtn.querySelector(".tooltip-text");

    // Create a portal for the tooltip and add it to the body
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
      event.preventDefault(); // Prevent following <a> link
      event.stopPropagation(); // Prevent propagation to parent elements
    });

    // Load the wishlist from localStorage
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];

    // Update the button class and tooltip based on whether the item is in the wishlist
    if (wishlist.includes(propertyId)) {
      wishlistBtn.classList.add("in-wishlist");
      updateTooltipText(true);
    } else {
      updateTooltipText(false);
    }

    wishlistBtn.addEventListener("click", function () {
      let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
      const isInWishlist = wishlist.includes(propertyId);

      // Add or remove the property from the wishlist
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

  // Function to sync the wishlist with the server and reload the page if needed
  function updateWishlistOnServer(wishlist) {
    fetch(wishlistData.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=set_wishlist_ids&wishlist_ids=${JSON.stringify(wishlist)}&nonce=${wishlistData.nonce}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Favoritos sincronizados con el servidor:", data);

        // Reload the page if we're on the favorites page
        if (window.location.pathname === "/favoritos/") {
          location.reload();
        }
      })
      .catch((error) => {
        console.error("Error al sincronizar los favoritos:", error);
      });
  }
});
