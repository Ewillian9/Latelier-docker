Table artwork {
	id integer [ pk, increment, not null, unique ]
	artist_id integer [ not null ]
	title varchar [ not null ]
	description text [ not null ]
	created_at timestamp [ not null ]
	updated_at timestamp [ not null ]
}

Table artwork_image {
	id integer [ pk, increment, not null, unique ]
	artwork_id integer [ not null ]
	image_name varchar [ not null ]
	image_size varchar [ not null ]
	created_at timestamp [ not null ]
	legend varchar
}

Table comment {
	id integer [ pk, increment, not null, unique ]
	artwork_id integer [ not null ]
	commenter_id integer [ not null ]
	content text [ not null ]
	created_at timestamp [ not null ]
	updated_at timestamp [ not null ]
}

Table user {
	id integer [ pk, increment, not null, unique ]
	email varchar [ not null ]
	roles json [ not null ]
	password varchar [ not null ]
	username varchar [ not null ]
	created_at timestamp [ not null ]
	updated_at timestamp [ not null ]
	is_verified boolean [ not null ]
}

Table conversation {
	id integer [ pk, increment, not null, unique ]
	artwork_id integer [ not null ]
	client_id integer [ not null ]
	artist_id integer [ not null ]
	order_id integer [ not null ]
	created_at timestamp [ not null ]
	updated_at timestamp [ not null ]
}

Table message {
	id integer [ pk, increment, not null, unique ]
	sender_id integer [ not null ]
	conversation_id integer [ not null ]
	content text [ not null ]
	created_at timestamp [ not null ]
}

Table order {
	id integer [ pk, increment, not null, unique ]
	artwork_id integer [ not null ]
	client_id integer [ not null ]
	status varchar [ not null ]
	created_at timestamp [ not null ]
	updated_at timestamp [ not null ]
}

Ref fk_artwork_image_artwork_id_artwork {
	artwork_image.artwork_id > artwork.id [ delete: no action, update: no action ]
}

Ref fk_comment_artwork_id_artwork {
	comment.artwork_id > artwork.id [ delete: no action, update: no action ]
}

Ref fk_artwork_artist_id_user {
	artwork.artist_id > user.id [ delete: no action, update: no action ]
}

Ref fk_comment_commenter_id_user {
	comment.commenter_id > user.id [ delete: no action, update: no action ]
}

Ref fk_conversation_client_id_user {
	conversation.client_id > user.id [ delete: no action, update: no action ]
}

Ref fk_message_sender_id_user {
	message.sender_id > user.id [ delete: no action, update: no action ]
}

Ref fk_message_conversation_id_conversation {
	message.conversation_id > conversation.id [ delete: no action, update: no action ]
}

Ref fk_conversation_artist_id_user {
	conversation.artist_id > user.id [ delete: no action, update: no action ]
}

Ref fk_conversation_artwork_id_artwork {
	conversation.artwork_id > artwork.id [ delete: no action, update: no action ]
}

Ref fk_order_client_id_user {
	order.client_id > user.id [ delete: no action, update: no action ]
}

Ref fk_order_artwork_id_artwork {
	order.artwork_id > artwork.id [ delete: no action, update: no action ]
}

Ref fk_conversation_order_id_order {
	conversation.order_id - order.id [ delete: no action, update: no action ]
}