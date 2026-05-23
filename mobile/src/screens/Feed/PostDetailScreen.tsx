import { useCallback, useEffect, useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { useBottomTabBarHeight } from '@react-navigation/bottom-tabs';

import { CommentCard } from '../../components/feed/CommentCard';
import { PostCard } from '../../components/feed/PostCard';
import { ReportContentModal } from '../../components/feed/ReportContentModal';
import { AppButton } from '../../components/ui/AppButton';
import { AppCard } from '../../components/ui/AppCard';
import { AppInput } from '../../components/ui/AppInput';
import { AppText } from '../../components/ui/AppText';
import { EmptyState } from '../../components/ui/EmptyState';
import { ErrorMessage } from '../../components/ui/ErrorMessage';
import { LoadingState } from '../../components/ui/LoadingState';
import { Screen } from '../../components/ui/Screen';
import { theme } from '../../config/theme';
import { useAuth } from '../../hooks/useAuth';
import { FeedStackParamList } from '../../navigation/navigation.types';
import { toApiError } from '../../services/api/apiError';
import { feedService } from '../../services/feed/feedService';
import { Commentaire, CreateReportPayload, Pagination, Post, ReactionType } from '../../types/feed.types';

type PostDetailScreenProps = NativeStackScreenProps<FeedStackParamList, 'PostDetail'>;

export function PostDetailScreen({ navigation, route }: PostDetailScreenProps) {
  const { postId } = route.params;
  const tabBarHeight = useBottomTabBarHeight();
  const { user } = useAuth();
  const [post, setPost] = useState<Post | null>(null);
  const [comments, setComments] = useState<Commentaire[]>([]);
  const [commentsPagination, setCommentsPagination] = useState<Pagination | null>(null);
  const [commentContent, setCommentContent] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreatingComment, setIsCreatingComment] = useState(false);
  const [isLoadingMoreComments, setIsLoadingMoreComments] = useState(false);
  const [isReacting, setIsReacting] = useState(false);
  const [reportTarget, setReportTarget] = useState<{ type: 'Post'; id: number } | { type: 'Commentaire'; id: number } | null>(null);
  const [isReporting, setIsReporting] = useState(false);
  const [reportError, setReportError] = useState<string | null>(null);

  const loadPostDetail = useCallback(async () => {
    setError(null);
    setIsLoading(true);

    try {
      const [loadedPost, loadedComments] = await Promise.all([
        feedService.getPost(postId),
        feedService.listComments(postId),
      ]);

      setPost(loadedPost);
      setComments(loadedComments.comments);
      setCommentsPagination(loadedComments.pagination);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoading(false);
    }
  }, [postId]);

  useEffect(() => {
    void loadPostDetail();
  }, [loadPostDetail]);

  async function createComment() {
    const trimmedContent = commentContent.trim();

    setError(null);

    if (!trimmedContent) {
      setError('Le contenu du commentaire est requis.');
      return;
    }

    setIsCreatingComment(true);

    try {
      await feedService.createComment(postId, { content: trimmedContent });
      setCommentContent('');
      const [updatedPost, updatedComments] = await Promise.all([
        feedService.getPost(postId),
        feedService.listComments(postId),
      ]);
      setPost(updatedPost);
      setComments(updatedComments.comments);
      setCommentsPagination(updatedComments.pagination);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsCreatingComment(false);
    }
  }

  async function loadMoreComments() {
    if (!commentsPagination || commentsPagination.page >= commentsPagination.pages || isLoadingMoreComments) {
      return;
    }

    setIsLoadingMoreComments(true);
    setError(null);

    try {
      const result = await feedService.listComments(postId, commentsPagination.page + 1);
      setCommentsPagination(result.pagination);
      setComments((currentComments) => [...currentComments, ...result.comments]);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsLoadingMoreComments(false);
    }
  }

  async function reactToPost(type: ReactionType) {
    await updatePostReaction(() => feedService.setReaction(postId, type));
  }

  async function removeReaction() {
    await updatePostReaction(() => feedService.removeReaction(postId));
  }

  async function updatePostReaction(action: () => Promise<Post>) {
    setIsReacting(true);
    setError(null);

    try {
      setPost(await action());
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
    } finally {
      setIsReacting(false);
    }
  }

  async function updatePost(updatedContent: string) {
    setError(null);

    try {
      setPost(await feedService.updatePost(postId, { content: updatedContent }));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  async function deletePost() {
    setError(null);

    try {
      await feedService.deletePost(postId);
      navigation.goBack();
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  async function updateComment(commentId: number, updatedContent: string) {
    setError(null);

    try {
      const updatedComment = await feedService.updateComment(commentId, { content: updatedContent });
      setComments((currentComments) => currentComments.map((comment) => (
        comment.id === updatedComment.id ? updatedComment : comment
      )));
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  async function deleteComment(commentId: number) {
    setError(null);

    try {
      await feedService.deleteComment(commentId);
      const [updatedPost, updatedComments] = await Promise.all([
        feedService.getPost(postId),
        feedService.listComments(postId),
      ]);
      setPost(updatedPost);
      setComments(updatedComments.comments);
      setCommentsPagination(updatedComments.pagination);
    } catch (caughtError) {
      setError(toApiError(caughtError).message);
      throw caughtError;
    }
  }

  async function submitReport(payload: CreateReportPayload) {
    if (reportTarget === null) {
      return;
    }

    setIsReporting(true);
    setReportError(null);

    try {
      if (reportTarget.type === 'Post') {
        await feedService.reportPost(reportTarget.id, payload);
      } else {
        await feedService.reportComment(reportTarget.id, payload);
      }

      setReportTarget(null);
      Alert.alert('Signalement envoyé', "L'équipe de modération pourra traiter ce contenu.");
    } catch (caughtError) {
      setReportError(toApiError(caughtError).message);
      throw caughtError;
    } finally {
      setIsReporting(false);
    }
  }

  return (
    <Screen style={styles.screen}>
      <ReportContentModal
        error={reportError}
        loading={isReporting}
        targetLabel={reportTarget?.type ?? 'Post'}
        visible={reportTarget !== null}
        onClose={() => {
          setReportTarget(null);
          setReportError(null);
        }}
        onSubmit={submitReport}
      />
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.keyboard}
      >
        <ScrollView
          contentContainerStyle={[styles.content, { paddingBottom: tabBarHeight + theme.spacing['2xl'] }]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.header}>
            <View style={styles.titleBlock}>
              <AppText style={styles.kicker}>Discussion</AppText>
              <AppText variant="title">Conversation du Klub</AppText>
              <AppText variant="subtitle">Lis le Post, réagis, puis rejoins le fil de commentaires.</AppText>
            </View>
            <AppButton label="Retour" onPress={() => navigation.goBack()} variant="secondary" />
          </View>

          <ErrorMessage message={error} />

          {isLoading ? <LoadingState message="Chargement de la discussion..." /> : null}

          {post ? (
            <PostCard
              canManage={post.author.id === user?.id}
              disabled={isReacting}
              onDelete={deletePost}
              onReact={reactToPost}
              onReport={() => setReportTarget({ type: 'Post', id: post.id })}
              onRemoveReaction={removeReaction}
              onUpdate={updatePost}
              post={post}
            />
          ) : null}

          <AppCard style={styles.commentForm}>
            <View style={styles.commentFormHeader}>
              <AppText style={styles.commentFormTitle}>Répondre au Post</AppText>
              <AppText variant="muted">{commentContent.trim().length}/500</AppText>
            </View>
            <AppInput
              autoCapitalize="sentences"
              label="Commentaire"
              maxLength={500}
              multiline
              onChangeText={setCommentContent}
              placeholder="Ta réponse..."
              style={styles.textArea}
              textAlignVertical="top"
              value={commentContent}
            />
            <AppButton label="Commenter" loading={isCreatingComment} onPress={createComment} />
          </AppCard>

          <View style={styles.commentsSection}>
            <AppText style={styles.sectionTitle}>Commentaires</AppText>
            {comments.length === 0 && !isLoading ? (
              <EmptyState
                icon="chatbubble-ellipses-outline"
                message="Sois le premier à répondre à ce Post."
                title="Aucun commentaire"
              />
            ) : null}
            {comments.map((comment) => (
              <CommentCard
                canManage={comment.author.id === user?.id}
                comment={comment}
                key={comment.id}
                onDelete={() => deleteComment(comment.id)}
                onReport={() => setReportTarget({ type: 'Commentaire', id: comment.id })}
                onUpdate={(updatedContent) => updateComment(comment.id, updatedContent)}
              />
            ))}
            {commentsPagination && commentsPagination.page < commentsPagination.pages ? (
              <AppButton
                label="Charger plus de commentaires"
                loading={isLoadingMoreComments}
                onPress={loadMoreComments}
                variant="secondary"
              />
            ) : null}
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  screen: {
    padding: 0,
  },
  keyboard: {
    flex: 1,
  },
  content: {
    gap: theme.spacing.lg,
    padding: theme.spacing.xl,
  },
  header: {
    alignItems: 'flex-start',
    gap: theme.spacing.md,
  },
  titleBlock: {
    gap: theme.spacing.sm,
  },
  kicker: {
    color: theme.colors.accent,
    fontSize: theme.typography.sizes.sm,
    fontWeight: theme.typography.weights.bold,
    textTransform: 'uppercase',
  },
  commentForm: {
    gap: theme.spacing.md,
  },
  commentFormHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  commentFormTitle: {
    fontSize: theme.typography.sizes.md,
    fontWeight: theme.typography.weights.bold,
  },
  textArea: {
    minHeight: 88,
    paddingTop: theme.spacing.md,
  },
  commentsSection: {
    gap: theme.spacing.md,
  },
  sectionTitle: {
    fontSize: theme.typography.sizes.lg,
    fontWeight: theme.typography.weights.bold,
  },
});
