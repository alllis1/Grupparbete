
async function toggleFavorite(button) {

    
    button.disabled = true;

    const placeId = button.dataset.placeId;

    try {

        const response = await fetch('/Grupparbete/toggle_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ place_id: placeId })
        });

        if (!response.ok) {
            throw new Error('Serverfel');
        }

        const result = await response.json();

        if (result.status === 'success') {

            if (result.isFavorite) {
                button.classList.add('active');
                button.textContent = '♥';
            } else {
                button.classList.remove('active');
                button.textContent = '♡';
            }

        } else {
            alert(result.message || 'Ett fel uppstod.');
        }

    } catch (error) {
        console.error('Fel:', error);
        alert('Kunde inte uppdatera favoriter.');
    } finally {
        
        button.disabled = false;
    }
}
